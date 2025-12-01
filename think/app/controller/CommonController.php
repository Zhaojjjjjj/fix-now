<?php

namespace app\controller;

use think\facade\Filesystem;

class CommonController extends _Controller
{
    /**
     * 文件上传
     */
    public function uploadAction()
    {
        $file = request()->file('file');

        if (!$file) {
            return $this->fail('没有选择上传文件');
        }

        try {
            // 验证文件规则  1000MB = 1048576000  500MB = 524288000
            $result = validate(['file' => ['fileSize:10240000,fileExt:gif,jpg,png']])->check(['file' => $file]);

            if ($result) {
                $saveName = Filesystem::disk('public')
                    ->putFile('uploads', $file, 'uniqid');
                $img      = '/storage/' . $saveName;

                $url = [
                    'url'     => $img,
                    'fullurl' => $img,
                ];
            }

            /** @var $url */
            return $this->success('', $url);
        } catch (\Throwable $th) {
            return $this->fail($th->getMessage());
        }
    }
}
