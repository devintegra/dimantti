<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utilerias
 *
 * @author rhm
 */
class Utilerias
{
    public function guardarMarca($mysqli, $pk_marca, $nombre)
    {

        $error = 0;

        $qmarca = "SELECT * FROM ct_marcas WHERE pk_marca = $pk_marca AND estado = 1";

        if (!$rmarca = $mysqli->query($qmarca)) {
            $error = 4;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
            exit;
        }

        if ($rmarca->num_rows > 0) {
            if (!$mysqli->query("UPDATE ct_marcas SET nombre = '$nombre' WHERE pk_marca = $pk_marca")) {
                $error = 1;
            }
        } else {
            if (!$mysqli->query("INSERT INTO ct_marcas(pk_marca, nombre) VALUES($pk_marca, '$nombre')")) {
                $error = 1;
            }
        }

        return $error;
    }


    public function guardarCategoria($mysqli, $pk_categoria, $nombre)
    {

        $error = 0;

        $qcategoria = "SELECT * FROM ct_categorias WHERE pk_categoria = $pk_categoria AND estado = 1";

        if (!$rcategoria = $mysqli->query($qcategoria)) {
            $error = 4;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
            exit;
        }

        if ($rcategoria->num_rows > 0) {
            if (!$mysqli->query("UPDATE ct_categorias SET nombre = '$nombre' WHERE pk_categoria = $pk_categoria")) {
                $error = 1;
            }
        } else {
            if (!$mysqli->query("INSERT INTO ct_categorias(pk_categoria, nombre) VALUES($pk_categoria, '$nombre')")) {
                $error = 1;
            }
        }


        return $error;
    }


    public function guardarProducto($mysqli, $pk_producto, $fk_marca, $fk_categoria, $descripcion, $costo, $inventario, $clave_sat, $precio, $precio2, $precio3, $precio4, $codigobarras, $destacado)
    {

        $error = 0;

        $destacado = (int)$destacado;
        if ($destacado != 0 || $destacado != 1) {
            $destacado = 0;
        }

        $qproducto = "SELECT * FROM ct_productos WHERE pk_producto = $pk_producto AND estado = 1";

        if (!$rproducto = $mysqli->query($qproducto)) {
            $error = 4;
            $descripcion = "Hubo un problema, porfavor vuelva a intentarlo";
            exit;
        }

        if ($rproducto->num_rows > 0) {
            if (!$mysqli->query("UPDATE ct_productos SET nombre = '$descripcion', marca = $fk_marca, fk_categoria = $fk_categoria, clave = '$codigobarras', costo = $costo, inventario = $inventario, clave_producto_sat = '$clave_sat', precio = $precio, precio2 = $precio2, precio3 = $precio3, precio4 = $precio4, codigobarras = '$codigobarras', destacado = $destacado WHERE pk_producto = $pk_producto AND estado = 1")) {
                $error = 1;
            }
        } else {
            if (!$mysqli->query("INSERT INTO ct_productos(pk_producto, nombre, marca, fk_categoria, fk_subcategoria, clave, costo, fk_comercio, inventario, clave_producto_sat, clave_unidad_sat, precio, precio2, precio3, precio4, codigobarras, destacado, largo, ancho, grueso, peso) VALUES($pk_producto, '$descripcion', $fk_marca, $fk_categoria, 1, '$codigobarras', $costo, 1, $inventario, '$clave_sat', '', $precio, $precio2, $precio3, $precio4, '$codigobarras', $destacado, 0, 0, 0, 0)")) {
                $error = 1;
            }
        }

        return $error;
    }


    public function guardarProductoImagen($mysqli, $pk_producto, $imagen, $orden)
    {

        $error = 0;

        if (!$mysqli->query("INSERT INTO rt_imagenes_productos(fk_producto, imagen, orden) VALUES($pk_producto, '$imagen', $orden)")) {
            $error = 1;
        }


        return $error;
    }
}
