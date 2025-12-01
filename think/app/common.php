<?php
// 应用公共文件

function getChildren($data, int $pId = 0)
{
    $result = [];

    foreach ($data as $v) {
        if ($v['parent_id'] == $pId) {
            $child         = getChildren($data, $v['id']);
            $v['children'] = $child ?: '';
            $result[]      = $v;
        }
    }

    return $result;

}