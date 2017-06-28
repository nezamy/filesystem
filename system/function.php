<?php
function df($k=null, $v=null, $path='')
{
    $df = System\DF::instance($path);
    if($k != null && $v != null){
        return $df->save($k, $v);
    }

    if($k != null){
        return $df->get($k);
    }

    return $df;
}