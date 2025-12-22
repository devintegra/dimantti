<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('fpdf.php');
define('EURO', chr(128));
define('EURO_VAL', 6.55957);

// Xavier Nicolay 2004
// Version 1.02

//////////////////////////////////////
// Public functions                 //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class PDF_Invoice extends FPDF
{
    // private variables
    var $colonnes;
    var $format;
    var $angle = 0;


    // private functions
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c ',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    // public functions
    function sizeOfText($texte, $largeur)
    {
        $index    = 0;
        $nb_lines = 0;
        $loop     = TRUE;
        while ($loop) {
            $pos = strpos($texte, "\n");
            if (!$pos) {
                $loop  = FALSE;
                $ligne = $texte;
            } else {
                $ligne  = substr($texte, $index, $pos);
                $texte = substr($texte, $pos + 1);
            }
            $length = floor($this->GetStringWidth($ligne));
            $res = 1 + floor($length / $largeur);
            $nb_lines += $res;
        }
        return $nb_lines;
    }

    protected $T128;                                         // Tableau des codes 128
    protected $ABCset = "";                                  // jeu des caractères éligibles au C128
    protected $Aset = "";                                    // Set A du jeu des caractères éligibles
    protected $Bset = "";                                    // Set B du jeu des caractères éligibles
    protected $Cset = "";                                    // Set C du jeu des caractères éligibles
    protected $SetFrom;                                      // Convertisseur source des jeux vers le tableau
    protected $SetTo;                                        // Convertisseur destination des jeux vers le tableau
    protected $JStart = array("A" => 103, "B" => 104, "C" => 105); // Caractères de sélection de jeu au début du C128
    protected $JSwap = array("A" => 101, "B" => 100, "C" => 99);   // Caractères de changement de jeu

    //____________________________ Extension du constructeur _______________________
    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {

        parent::__construct($orientation, $unit, $size);

        $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
        $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
        $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
        $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
        $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
        $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
        $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
        $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
        $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
        $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
        $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
        $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
        $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
        $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
        $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
        $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
        $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
        $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
        $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
        $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
        $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
        $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
        $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
        $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
        $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
        $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
        $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
        $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
        $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
        $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
        $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
        $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
        $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
        $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
        $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
        $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
        $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
        $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
        $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
        $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
        $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
        $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
        $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
        $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
        $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
        $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
        $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
        $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
        $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
        $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
        $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
        $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
        $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
        $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
        $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
        $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
        $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
        $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
        $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
        $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
        $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
        $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
        $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
        $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
        $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
        $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
        $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
        $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
        $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
        $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
        $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
        $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
        $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
        $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
        $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
        $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
        $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
        $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
        $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
        $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
        $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
        $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
        $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
        $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
        $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
        $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
        $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
        $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
        $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
        $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
        $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
        $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
        $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
        $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
        $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
        $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
        $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
        $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
        $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
        $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
        $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]
        $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
        $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
        $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
        $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
        $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
        $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
        $this->T128[] = array(2, 1);                       //107 : [END BAR]

        for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
            $this->ABCset .= chr($i);
        }
        $this->Aset = $this->ABCset;
        $this->Bset = $this->ABCset;

        for ($i = 0; $i <= 31; $i++) {
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
        }
        for ($i = 96; $i <= 127; $i++) {
            $this->ABCset .= chr($i);
            $this->Bset .= chr($i);
        }
        for ($i = 200; $i <= 210; $i++) {                                           // controle 128
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
            $this->Bset .= chr($i);
        }
        $this->Cset = "0123456789" . chr(206);

        for ($i = 0; $i < 96; $i++) {                                                   // convertisseurs des jeux A & B
            @$this->SetFrom["A"] .= chr($i);
            @$this->SetFrom["B"] .= chr($i + 32);
            @$this->SetTo["A"] .= chr(($i < 32) ? $i + 64 : $i - 32);
            @$this->SetTo["B"] .= chr($i);
        }
        for ($i = 96; $i < 107; $i++) {                                                 // contrôle des jeux A & B
            @$this->SetFrom["A"] .= chr($i + 104);
            @$this->SetFrom["B"] .= chr($i + 104);
            @$this->SetTo["A"] .= chr($i);
            @$this->SetTo["B"] .= chr($i);
        }
    }

    function Code128($x, $y, $code, $w, $h)
    {
        $Aguid = "";                                                                      // Création des guides de choix ABC
        $Bguid = "";
        $Cguid = "";
        for ($i = 0; $i < strlen($code); $i++) {
            $needle = substr($code, $i, 1);
            $Aguid .= ((strpos($this->Aset, $needle) === false) ? "N" : "O");
            $Bguid .= ((strpos($this->Bset, $needle) === false) ? "N" : "O");
            $Cguid .= ((strpos($this->Cset, $needle) === false) ? "N" : "O");
        }

        $SminiC = "OOOO";
        $IminiC = 4;

        $crypt = "";
        while ($code > "") {
            // BOUCLE PRINCIPALE DE CODAGE
            $i = strpos($Cguid, $SminiC);                                                // forçage du jeu C, si possible
            if ($i !== false) {
                $Aguid[$i] = "N";
                $Bguid[$i] = "N";
            }

            if (substr($Cguid, 0, $IminiC) == $SminiC) {                                  // jeu C
                $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
                $made = strpos($Cguid, "N");                                             // étendu du set C
                if ($made === false) {
                    $made = strlen($Cguid);
                }
                if (fmod($made, 2) == 1) {
                    $made--;                                                            // seulement un nombre pair
                }
                for ($i = 0; $i < $made; $i += 2) {
                    $crypt .= chr(strval(substr($code, $i, 2)));                          // conversion 2 par 2
                }
                $jeu = "C";
            } else {
                $madeA = strpos($Aguid, "N");                                            // étendu du set A
                if ($madeA === false) {
                    $madeA = strlen($Aguid);
                }
                $madeB = strpos($Bguid, "N");                                            // étendu du set B
                if ($madeB === false) {
                    $madeB = strlen($Bguid);
                }
                $made = (($madeA < $madeB) ? $madeB : $madeA);                         // étendu traitée
                $jeu = (($madeA < $madeB) ? "B" : "A");                                // Jeu en cours

                $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

                $crypt .= strtr(substr($code, 0, $made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

            }
            $code = substr($code, $made);                                           // raccourcir légende et guides de la zone traitée
            $Aguid = substr($Aguid, $made);
            $Bguid = substr($Bguid, $made);
            $Cguid = substr($Cguid, $made);
        }                                                                          // FIN BOUCLE PRINCIPALE

        $check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
        for ($i = 0; $i < strlen($crypt); $i++) {
            $check += (ord($crypt[$i]) * $i);
        }
        $check %= 103;

        $crypt .= chr($check) . chr(106) . chr(107);                               // Chaine cryptée complète

        $i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
        $modul = $w / $i;

        for ($i = 0; $i < strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
            $c = $this->T128[ord($crypt[$i])];
            for ($j = 0; $j < count($c); $j++) {
                $this->Rect($x, $y, $c[$j] * $modul, $h, "F");
                $x += ($c[$j++] + $c[$j]) * $modul;
            }
        }
    }


    function addStotalVentaDast($total)
    {

        $x1 = 104;
        $y1 = 126;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 8, 'Subtotal', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }

    function addOtrosVentaDast($total)
    {

        $x1 = 104;
        $y1 = 134;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 8, 'Otros', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }

    function addTotalVentaDast($total)
    {

        $x1 = 104;
        $y1 = 143;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }

    // Company
    function addSociete($nom, $adresse)
    {
        $adresse = utf8_decode($adresse);
        $x1 = 5;
        $y1 = 20;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $length = $this->GetStringWidth($nom);
        //$this->Cell( $length, 2, $nom);
        $this->SetXY($x1, $y1 + 4);
        $this->SetFont('Arial', '', 8);
        $length = 100;
        //Coordonnées de la société
        $lignes = $this->sizeOfText($adresse, $length);
        $this->MultiCell($length, 4, $adresse, 0, 'C', false);
    }

    function addSocieteTicket($nom, $adresse)
    {
        $adresse = utf8_decode($adresse);
        $x1 = 0;
        $y1 = 20;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $length = $this->GetStringWidth($nom);
        //$this->Cell( $length, 2, $nom);
        $this->SetXY($x1, $y1 + 4);
        $this->SetFont('Arial', '', 8);
        $length = 80;
        //Coordonnées de la société
        $lignes = $this->sizeOfText($adresse, $length);
        $this->MultiCell($length, 4, $adresse, 0, 'C', false);
    }

    function addSocieteTicketXY($nom, $adresse, $x, $y)
    {
        $adresse = utf8_decode($adresse);
        $x1 = $x;
        $y1 = $y;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $length = $this->GetStringWidth($nom);
        //$this->Cell( $length, 2, $nom);
        $this->SetXY($x1, $y1 + 4);
        $this->SetFont('Arial', '', 8);
        $length = 80;
        //Coordonnées de la société
        $lignes = $this->sizeOfText($adresse, $length);
        $this->MultiCell($length, 4, $adresse, 0, 'C', false);
    }



    // Label and number of invoice/estimate
    function fact_dev($libelle, $num)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 6;
        $y2  = $y1 + 2;
        $mid = ($r1 + $r2) / 2;

        $texte  = $num;
        $texte = utf8_decode($texte);
        $szfont = 9;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }


    function fact_dev_prov($libelle, $num)
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 68;
        $y1  = 6;
        $y2  = $y1 + 2;
        $mid = ($r1 + $r2) / 2;

        $texte  = $libelle . ": " . $num;
        $texte = utf8_decode($texte);
        $szfont = 12;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }


    function fact_devo($libelle, $num)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 5;
        $y2  = $y1 + 2;
        $mid = ($r1 + $r2) / 2;

        $texte  = $num;
        $texte = utf8_decode($texte);
        $szfont = 9;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }



    function fact_dev_venta($libelle, $num)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 7;
        $y2  = $y1 + 2;
        $mid = ($r1 + $r2) / 2;

        $texte  = $num;
        $texte = utf8_decode($texte);
        $szfont = 9;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1 + 8, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 10);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }


    function RotatedText($x, $y, $txt, $angle, $fontsize)
    {
        $this->SetFont("Arial", "", $fontsize);
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }


    function tipo_pago($num)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 16;
        $y2  = 8;
        $mid = ($r1 + $r2) / 2;

        $texte  = $num;
        $texte = utf8_decode($texte);
        $szfont = 9;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }

    function contacto($libelle, $num)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 26;
        $y2  = 8;
        $mid = ($r1 + $r2) / 2;

        $texte  = $num;
        $texte = utf8_decode($texte);
        $szfont = 9;
        $loop   = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }


    // Estimate
    function addDevis($numdev)
    {
        $string = sprintf("DEV%04d", $numdev);
        $this->fact_dev("Devis", $string);
    }

    // Invoice
    function addFacture($numfact)
    {
        $string = sprintf("FA%04d", $numfact);
        $this->fact_dev("Facture", $string);
    }

    function addDate($date)
    {
        $r1  = $this->w - 61;
        $r2  = $r1 + 30;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Fecha", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 8);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }

    function addInfoGeneral($date, $x, $y, $title)
    {
        $r1  = $this->w - 61;
        $r2  = $r1 + $x;
        $y1  = $y;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "$title", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }


    function addDateProv($date)
    {
        $r1  = $this->w - 61;
        $r2  = $r1 + 30;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Fecha", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }


    function addHour($date)
    {
        $r1  = $this->w - 35;
        $r2  = $r1 + 30;
        $y1  = 17;
        $y2  = $y1;
        $mid = 43.5;
        $this->RoundedRect($r1, 35, ($r2 - $r1), 15, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 21);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Hora", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 27);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }

    function addClient($ref)
    {
        $r1  = $this->w - 31;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Id Suc", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $ref, 0, 0, "C");
    }

    function addPageNumber($page)
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Entrada", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $page, 0, 0, "C");
    }

    function addVentaNumber($page)
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Venta", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $page, 0, 0, "C");
    }

    function addCompraNumber($page)
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Compra", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $page, 0, 0, "C");
    }

    function addTransferNumber($page)
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "Transfer", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $page, 0, 0, "C");
    }



    // Client address
    function addClientAdresse($adresse)
    {
        $adresse = utf8_decode($adresse);
        $r1     = $this->w - 80;
        $r2     = $r1 + 68;
        $y1     = 40;
        $this->SetXY($r1, $y1);
        $this->MultiCell(60, 4, $adresse);
    }

    // Mode of payment
    function addReglement($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 10;
        $r2  = $r1 + 60;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Proveedor", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }


    function addCliente($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 5;
        $r2  = $r1 + 53;
        $r3 = $r1 + 139;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, 37, ($r3 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, 42.5, $r3, 42.5);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 1);
        $this->SetFont("Arial", "B", 9);
        $this->Cell(10, 4, "Cliente Nombre", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 5);
        $this->SetFont("Arial", "", 9);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }


    function addClienteTel($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 50;
        $r2  = $r1 + 53;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        //$this->RoundedRect($r1, 37, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        //$this->Line( $r1, 42.5, $r2, 42.5);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 1);
        $this->SetFont("Arial", "B", 9);
        $this->Cell(10, 4, "Cliente Telefono", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 5);
        $this->SetFont("Arial", "", 9);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }


    function addClienteCorreo($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 92;
        $r2  = $r1 + 53;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        //$this->RoundedRect($r1, 37, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        //$this->Line( $r1, 42.5, $r2, 42.5);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 1);
        $this->SetFont("Arial", "B", 9);
        $this->Cell(10, 4, "Cliente Correo", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, 37 + 5);
        $this->SetFont("Arial", "", 9);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }

    function addBene($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 5;
        $r2  = $r1 + 53;
        $r3  = $r1 + 107;
        $y1  = 49;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r3 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r3, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Beneficiario Nombre", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }

    function addBeneTel($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 59;
        $r2  = $r1 + 53;
        $r3  = $r1 + 107;
        $y1  = 49;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        //$this->RoundedRect($r1, $y1, ($r3 - $r1), ($y2-$y1), 2.5, 'D');
        //$this->Line( $r1, $mid, $r3, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Beneficiario Telefono", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }



    function addHora($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 114;
        $r2  = $r1 + 30;
        $y1  = 49;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Hora", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }


    function addFecha($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 114;
        $r2  = $r1 + 30;
        $y1  = 37;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Fecha", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }



    function addDireccion($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 5;
        $r2  = $r1 + 139;
        $y1  = 61;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Direccion", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }


    function addObservaciones($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 10;
        $r2  = $r1 + 139;
        $y1  = 50;
        $y2  = $y1 + 10;
        $y3 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y3 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Observaciones", 0, 0, "C");
        $this->SetXY($r1, $y1 + 5);
        $this->SetFont("Arial", "", 8);
        $this->MultiCell(($r2 - $r1), 4, $mode, 0, "L", false);
    }


    function addObservacionesDevolucion($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 5;
        $r2  = $r1 + 139;
        $y1  = 130;
        $y2  = $y1 + 10;
        $y3 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y3 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Observaciones", 0, 0, "C");
        $this->SetXY($r1, $y1 + 5);
        $this->SetFont("Arial", "", 8);
        $this->MultiCell(($r2 - $r1), 4, $mode, 0, "L", false);
    }

    /*
function addObservaciones( $mode )
{
   $mode=utf8_decode($mode);
    $r1  = 5;
    $r2  = $r1 + 104;
    $y1  = 165;
    $y2  = $y1+10;
    $y3= $y1+26;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y3-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Arial", "B", 10);
    $this->Cell(10,4, "Observaciones", 0, 0, "C");
    $this->SetXY( $r1 , $y1 + 5 );
    $this->SetFont( "Arial", "", 8);
    $this->MultiCell(($r2 - $r1),4,$mode, 0, "L", false);
}

*/


    function addDestino($mode)
    {
        $mode = utf8_decode($mode);
        $r1  = 10;
        $r2  = $r1 + 60;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Destino", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }

    function addOrigenDestino($mode, $title, $x, $y)
    {
        $mode = utf8_decode($mode);
        $r1  = $x;
        $r2  = $r1 + 60;
        $y1  = $y;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "$title", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }

    // Expiry date
    function addEcheance($date)
    {
        $r1  = 80;
        $r2  = $r1 + 40;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "Factura referencia", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }

    // VAT number
    function addNumTVA($tva)
    {
        $tva = utf8_decode($tva);
        $this->SetFont("Arial", "B", 10);
        $r1  = $this->w - 80;
        $r2  = $r1 + 70;
        $y1  = 80;
        $y2  = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + 16, $y1 + 1);
        $this->Cell(40, 4, "Pago", '', '', "C");
        $this->SetFont("Arial", "", 10);
        $this->SetXY($r1 + 16, $y1 + 5);
        $this->Cell(40, 5, $tva, '', '', "C");
    }

    function addReference($ref)
    {
        $this->SetFont("Arial", "", 10);
        $length = $this->GetStringWidth("Références : " . $ref);
        $r1  = 10;
        $r2  = $r1 + $length;
        $y1  = 92;
        $y2  = $y1 + 5;
        $this->SetXY($r1, $y1);
        $this->Cell($length, 4, "Références : " . $ref);
    }

    function addCols($tab)
    {
        global $colonnes;

        $r1  = 5;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 55;
        $y2  = $this->h - 55 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColsProv($tab)
    {
        global $colonnes;

        $r1  = 5;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 55;
        $y2  = $this->h - 75 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColsVenta($tab)
    {
        global $colonnes;

        $r1  = 5;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 55;
        $y2  = $this->h - 85 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColsTicket($tab)
    {
        global $colonnes;

        $r1  = 5;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 65;
        $y2  = $this->h - 45 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $this->SetFont("Arial", "", 7);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColse($tab)
    {
        global $colonnes;

        $r1  = 10;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 100;
        $y2  = $this->h - 50 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColsGeneral($tab, $x, $y)
    {
        global $colonnes;

        $r1  = $x;
        $r2  = $this->w - ($r1 * 2);
        $y1  = $y;
        $y2  = $this->h - 50 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addColsnew($tab)
    {
        global $colonnes;

        $r1  = 10;
        $r2  = $this->w - ($r1 * 2);
        $y1  = 10;
        $y2  = $this->h - 50 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        foreach ($tab as $lib => $pos) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }


    function addLineFormat($tab)
    {
        global $format, $colonnes;

        foreach ($colonnes as $lib => $pos) {
            if (isset($tab[$lib])) {
                $format[$lib] = $tab[$lib];
            }
        }
    }

    function lineVert($tab)
    {
        global $colonnes;

        reset($colonnes);
        $maxSize = 0;
        foreach ($colonnes as $lib => $pos) {
            $texte = $tab[$lib];
            $longCell = $pos - 2;
            $size = $this->sizeOfText($texte, $longCell);
            if ($size > $maxSize) {
                $maxSize = $size;
            }
        }

        return $maxSize;
    }

    // add a line to the invoice/estimate
    /*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
    function addLine($ligne, $tab)
    {
        global $colonnes, $format;

        $ordonnee     = 5;
        $maxSize      = $ligne;

        reset($colonnes);
        $this->SetFont("Arial", "", 9);
        foreach ($colonnes as $lib => $pos) {
            $longCell = $pos - 2;
            $texte = $tab[$lib];
            $length = $this->GetStringWidth($texte);
            $tailleTexte = $this->sizeOfText($texte, $length);
            $formText = $format[$lib];

            $this->SetXY($ordonnee, $ligne - 1);
            $this->MultiCell($longCell, 4, utf8_decode($texte), 0, $formText);

            if ($maxSize < $this->GetY()) {
                $maxSize = $this->GetY();
            }

            $ordonnee += $pos;
        }

        return ($maxSize - $ligne);
    }

    function addRemarque($remarque)
    {
        $this->SetFont("Arial", "", 10);
        $length = $this->GetStringWidth("Remarque : " . $remarque);
        $r1  = 10;
        $r2  = $r1 + $length;
        $y1  = $this->h - 45.5;
        $y2  = $y1 + 5;
        $this->SetXY($r1, $y1);
        $this->Cell($length, 4, "Remarque : " . $remarque);
    }

    function addCadreTVAs()
    {
        $this->SetFont("Arial", "B", 8);
        $r1  = 10;
        $r2  = $r1 + 120;
        $y1  = $this->h - 40;
        $y2  = $y1 + 20;
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $y1 + 4, $r2, $y1 + 4);
        $this->Line($r1 + 5,  $y1 + 4, $r1 + 5, $y2); // avant BASES HT
        $this->Line($r1 + 27, $y1, $r1 + 27, $y2);  // avant REMISE
        $this->Line($r1 + 43, $y1, $r1 + 43, $y2);  // avant MT TVA
        $this->Line($r1 + 63, $y1, $r1 + 63, $y2);  // avant % TVA
        $this->Line($r1 + 75, $y1, $r1 + 75, $y2);  // avant PORT
        $this->Line($r1 + 91, $y1, $r1 + 91, $y2);  // avant TOTAUX
        $this->SetXY($r1 + 9, $y1);
        $this->Cell(10, 4, "BASES HT");
        $this->SetX($r1 + 29);
        $this->Cell(10, 4, "REMISE");
        $this->SetX($r1 + 48);
        $this->Cell(10, 4, "MT TVA");
        $this->SetX($r1 + 63);
        $this->Cell(10, 4, "% TVA");
        $this->SetX($r1 + 78);
        $this->Cell(10, 4, "PORT");
        $this->SetX($r1 + 100);
        $this->Cell(10, 4, "TOTAUX");
        $this->SetFont("Arial", "B", 6);
        $this->SetXY($r1 + 93, $y2 - 8);
        $this->Cell(6, 0, "H.T.   :");
        $this->SetXY($r1 + 93, $y2 - 3);
        $this->Cell(6, 0, "T.V.A. :");
    }

    function addCadreEurosFrancs()
    {
        $r1  = $this->w - 70;
        $r2  = $r1 + 60;
        $y1  = $this->h - 40;
        $y2  = $y1 + 20;
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1 + 20,  $y1, $r1 + 20, $y2); // avant EUROS
        $this->Line($r1 + 20, $y1 + 4, $r2, $y1 + 4); // Sous Euros & Francs
        $this->Line($r1 + 38,  $y1, $r1 + 38, $y2); // Entre Euros & Francs
        $this->SetFont("Arial", "B", 8);
        $this->SetXY($r1 + 22, $y1);
        $this->Cell(15, 4, "EUROS", 0, 0, "C");
        $this->SetFont("Arial", "", 8);
        $this->SetXY($r1 + 42, $y1);
        $this->Cell(15, 4, "FRANCS", 0, 0, "C");
        $this->SetFont("Arial", "B", 6);
        $this->SetXY($r1, $y1 + 5);
        $this->Cell(20, 4, "TOTAL TTC", 0, 0, "C");
        $this->SetXY($r1, $y1 + 10);
        $this->Cell(20, 4, "ACOMPTE", 0, 0, "C");
        $this->SetXY($r1, $y1 + 15);
        $this->Cell(20, 4, "NET A PAYER", 0, 0, "C");
    }

    // remplit les cadres TVA / Totaux et la remarque
    // params  = array( "RemiseGlobale" => [0|1],
    //                      "remise_tva"     => [1|2...],  // {la remise s'applique sur ce code TVA}
    //                      "remise"         => value,     // {montant de la remise}
    //                      "remise_percent" => percent,   // {pourcentage de remise sur ce montant de TVA}
    //                  "FraisPort"     => [0|1],
    //                      "portTTC"        => value,     // montant des frais de ports TTC
    //                                                     // par defaut la TVA = 19.6 %
    //                      "portHT"         => value,     // montant des frais de ports HT
    //                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
    //                  "AccompteExige" => [0|1],
    //                      "accompte"         => value    // montant de l'acompte (TTC)
    //                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
    //                  "Remarque" => "texte"              // texte
    // tab_tva = array( "1"       => 19.6,
    //                  "2"       => 5.5, ... );
    // invoice = array( "px_unit" => value,
    //                  "qte"     => qte,
    //                  "tva"     => code_tva );
    function addTVAs($params, $tab_tva, $invoice)
    {
        $this->SetFont('Arial', '', 8);

        reset($invoice);
        $px = array();
        foreach ($invoice as $k => $prod) {
            $tva = $prod["tva"];
            @$px[$tva] += $prod["qte"] * $prod["px_unit"];
        }


        $prix     = array();
        $totalHT  = 0;
        $totalTTC = 0;
        $totalTVA = 0;
        $y = 261;
        reset($px);
        natsort($px);
        foreach ($px as $code_tva => $articleHT) {
            $tva = $tab_tva[$code_tva];
            $this->SetXY(17, $y);
            $this->Cell(19, 4, sprintf("%0.2F", $articleHT), '', '', 'R');

            if ($params["RemiseGlobale"] == 1) {
                if ($params["remise_tva"] == $code_tva) {
                    $this->SetXY(37.5, $y);

                    if ($params["remise"] > 0) {
                        $l_remise = (is_int($params["remise"])) ? $params["remise"] : sprintf("%0.2F", $params["remise"]);
                        $this->Cell(14.5, 4, $l_remise, '', '', 'R');
                        $articleHT -= $params["remise"];
                    } else if ($params["remise_percent"] > 0) {
                        $rp = $params["remise_percent"];
                        if ($rp > 1) $rp /= 100;
                        $rabais = $articleHT * $rp;
                        $articleHT -= $rabais;
                        $l_remise = (is_int($rabais)) ? $rabais : sprintf("%0.2F", $rabais);
                        $this->Cell(14.5, 4, $l_remise, '', '', 'R');
                    } else {
                        $this->Cell(14.5, 4, "ErrorRem", '', '', 'R');
                    }
                }
            }

            $totalHT += $articleHT;
            $totalTTC += $articleHT * (1 + $tva / 100);
            $tmp_tva = $articleHT * $tva / 100;
            $a_tva[$code_tva] = $tmp_tva;
            $totalTVA += $tmp_tva;

            $this->SetXY(11, $y);
            $this->Cell(5, 4, $code_tva);
            $this->SetXY(53, $y);
            $this->Cell(19, 4, sprintf("%0.2F", $tmp_tva), '', '', 'R');
            $this->SetXY(74, $y);
            $this->Cell(10, 4, sprintf("%0.2F", $tva), '', '', 'R');

            $y += 4;
        }


        if ($params["FraisPort"] == 1) {
            if ($params["portTTC"] > 0) {
                $pTTC = sprintf("%0.2F", $params["portTTC"]);
                $pHT  = sprintf("%0.2F", $pTTC / 1.196);
                $pTVA = sprintf("%0.2F", $pHT * 0.196);
                $this->SetFont('Arial', '', 6);
                $this->SetXY(85, 261);
                $this->Cell(6, 4, "HT : ", '', '', '');
                $this->SetXY(92, 261);
                $this->Cell(9, 4, $pHT, '', '', 'R');
                $this->SetXY(85, 265);
                $this->Cell(6, 4, "TVA : ", '', '', '');
                $this->SetXY(92, 265);
                $this->Cell(9, 4, $pTVA, '', '', 'R');
                $this->SetXY(85, 269);
                $this->Cell(6, 4, "TTC : ", '', '', '');
                $this->SetXY(92, 269);
                $this->Cell(9, 4, $pTTC, '', '', 'R');
                $this->SetFont('Arial', '', 8);
                $totalHT += $pHT;
                $totalTVA += $pTVA;
                $totalTTC += $pTTC;
            } else if ($params["portHT"] > 0) {
                $pHT  = sprintf("%0.2F", $params["portHT"]);
                $pTVA = sprintf("%0.2F", $params["portTVA"] * $pHT / 100);
                $pTTC = sprintf("%0.2F", $pHT + $pTVA);
                $this->SetFont('Arial', '', 6);
                $this->SetXY(85, 261);
                $this->Cell(6, 4, "HT : ", '', '', '');
                $this->SetXY(92, 261);
                $this->Cell(9, 4, $pHT, '', '', 'R');
                $this->SetXY(85, 265);
                $this->Cell(6, 4, "TVA : ", '', '', '');
                $this->SetXY(92, 265);
                $this->Cell(9, 4, $pTVA, '', '', 'R');
                $this->SetXY(85, 269);
                $this->Cell(6, 4, "TTC : ", '', '', '');
                $this->SetXY(92, 269);
                $this->Cell(9, 4, $pTTC, '', '', 'R');
                $this->SetFont('Arial', '', 8);
                $totalHT += $pHT;
                $totalTVA += $pTVA;
                $totalTTC += $pTTC;
            }
        }

        $this->SetXY(114, 266.4);
        $this->Cell(15, 4, sprintf("%0.2F", $totalHT), '', '', 'R');
        $this->SetXY(114, 271.4);
        $this->Cell(15, 4, sprintf("%0.2F", $totalTVA), '', '', 'R');

        $params["totalHT"] = $totalHT;
        $params["TVA"] = $totalTVA;
        $accompteTTC = 0;
        if ($params["AccompteExige"] == 1) {
            if ($params["accompte"] > 0) {
                $accompteTTC = sprintf("%.2F", $params["accompte"]);
                if (strlen($params["Remarque"]) == 0)
                    $this->addRemarque("Accompte de $accompteTTC Euros exigé à la commande.");
                else
                    $this->addRemarque($params["Remarque"]);
            } else if ($params["accompte_percent"] > 0) {
                $percent = $params["accompte_percent"];
                if ($percent > 1)
                    $percent /= 100;
                $accompteTTC = sprintf("%.2F", $totalTTC * $percent);
                $percent100 = $percent * 100;
                if (strlen($params["Remarque"]) == 0)
                    $this->addRemarque("Accompte de $percent100 % (soit $accompteTTC Euros) exigé à la commande.");
                else
                    $this->addRemarque($params["Remarque"]);
            } else
                $this->addRemarque("Drôle d'acompte !!! " . $params["Remarque"]);
        } else {
            if (strlen($params["Remarque"]) > 0)
                $this->addRemarque($params["Remarque"]);
        }
        $re  = $this->w - 50;
        $rf  = $this->w - 29;
        $y1  = $this->h - 40;
        $this->SetFont("Arial", "", 8);
        $this->SetXY($re, $y1 + 5);
        $this->Cell(17, 4, sprintf("%0.2F", $totalTTC), '', '', 'R');
        $this->SetXY($re, $y1 + 10);
        $this->Cell(17, 4, sprintf("%0.2F", $accompteTTC), '', '', 'R');
        $this->SetXY($re, $y1 + 14.8);
        $this->Cell(17, 4, sprintf("%0.2F", $totalTTC - $accompteTTC), '', '', 'R');
        $this->SetXY($rf, $y1 + 5);
        $this->Cell(17, 4, sprintf("%0.2F", $totalTTC * EURO_VAL), '', '', 'R');
        $this->SetXY($rf, $y1 + 10);
        $this->Cell(17, 4, sprintf("%0.2F", $accompteTTC * EURO_VAL), '', '', 'R');
        $this->SetXY($rf, $y1 + 14.8);
        $this->Cell(17, 4, sprintf("%0.2F", ($totalTTC - $accompteTTC) * EURO_VAL), '', '', 'R');
    }

    // add a watermark (temporary estimate, DUPLICATA...)
    // call this method first
    function temporaire($texte)
    {
        $this->SetFont('Arial', 'B', 50);
        $this->SetTextColor(203, 203, 203);
        $this->Rotate(45, 55, 190);
        $this->Text(55, 190, $texte);
        $this->Rotate(0);
        $this->SetTextColor(0, 0, 0);
    }


    function addTotal($total, $y)
    {

        $x1 = 110;
        $y1 = $y;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 8, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 15, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(19, 8, $total, 1, 0, 'R');
    }

    function addSubtotal($total)
    {

        $x1 = 104;
        $y1 = 125;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(20, 8, 'Subtotal', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }

    function addDescuento($total)
    {

        $x1 = 104;
        $y1 = 133;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(20, 8, 'Anticipo', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }


    function addTotalVenta($total)
    {

        $x1 = 104;
        $y1 = 141;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }


    function addSubTotalVenta($total)
    {

        $x1 = 104;
        $y1 = 141;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(20, 8, 'Otros', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }


    function addElTotalVenta($total)
    {

        $x1 = 104;
        $y1 = 149;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 8, $total, 1, 0, 'R');
    }





    function addIva($total)
    {

        $x1 = 110;
        $y1 = 152;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(15, 8, 'Iva', 1, 0, 'C');
        $this->SetXY($x1 + 15, $y1);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(19, 8, $total, 1, 0, 'R');
    }

    function addTotala($total)
    {

        $x1 = 120;
        $y1 = 214;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 40, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, $total, 1, 0, 'R');
    }

    function addTotalaProv($total)
    {

        $x1 = 120;
        $y1 = 225;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 40, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, $total, 1, 0, 'R');
    }

    function addTotale($total, $y)
    {

        $x1 = 140;
        $y1 = $y;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(20, 10, 'Total', 1, 0, 'C');
        $this->SetXY($x1 + 20, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, $total, 1, 0, 'R');
    }


    function addSaldo($total)
    {

        $x1 = 120;
        $y1 = 225;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, 'Saldo', 1, 0, 'C');
        $this->SetXY($x1 + 40, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, $total, 1, 0, 'R');
    }

    function addSaldoProv($total)
    {

        $x1 = 120;
        $y1 = 234;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, 'Saldo', 1, 0, 'C');
        $this->SetXY($x1 + 40, $y1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40, 10, $total, 1, 0, 'R');
    }

    function addImage($imagen)
    {
        $x1 = 15;
        $y1 = 5;
        $ancho = 60;
        $alto = 18;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }


    function addImageQR($imagen)
    {
        $x1 = 25;
        $y1 = 3;
        $ancho = 100;
        $alto = 100;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }


    function addImageQRI($imagen, $y)
    {
        $x1 = 110;
        $y1 = $y;
        $ancho = 35;
        $alto = 35;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }


    function addImageQRID($imagen, $x, $y)
    {
        $x1 = $x;
        $y1 = $y;
        $ancho = 35;
        $alto = 35;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }


    function addImageProducto($imagen, $x, $y)
    {
        $x1 = $x;
        $y1 = $y;
        $ancho = 35;
        $alto = 35;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'JPG');
    }


    function addFirma($imagen, $y)
    {
        $x1 = 32;
        $y1 = $y;
        $ancho = 50;
        $alto = 35;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }


    function addFirmanew($imagen, $y)
    {
        $x1 = 42;
        $y1 = $y;
        $ancho = 50;
        $alto = 35;

        $this->Image($imagen, $x1, $y1, $ancho, $alto, 'PNG');
    }



    function addPagare($cantidad, $letras)
    {
        //$nl = new NumeroALetras;
        //$letras=$nl->convertir($cantidadsf);

        $this->Rect(10, 226, 190, 50);
        $this->SetXY(10, 227);
        $pagare = utf8_decode("Debo(emos) y Pagaré(mos) incondicionalmente por este PAGARÉ a la orden de _______________________________________ , con domicilio en _______________________________________ del dìa __ de ______ del ____ La cantidad de $$cantidad ($letras). Valor de la mercancia que he(mos) recibido a mi (nuestra) entera satisfaccion. Este pagaré es marcantil y esta regido por la ley gral. de títulos y operaciones de crédito en el artículo 173 parte final y art. correlativos. Si este pagaré no es liquidado a su vencimiento causara intereses moratorios del ___ % mensual en base al art: 150 del codigo de comercio. Se cobrara el 20% sobre el importe del cheque devuelto");
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(190, 5, $pagare, 0);
        $this->SetXY(10, 256);
        $this->Cell(30, 10, "Nombre", 0, 0, 'L');
        $this->SetXY(10, 261);
        $this->Cell(30, 10, "Direccion", 0, 0, 'L');
        $this->SetXY(10, 266);
        $this->Cell(30, 10, "Ciudad", 0, 0, 'L');
        $this->SetXY(90, 261);
        $this->Cell(30, 10, "Acepto(amos)", 0, 0, 'L');
        $this->SetXY(90, 266);
        $this->Cell(30, 10, "Firma(s)_____________________________________", 0, 0, 'L');
    }
}
