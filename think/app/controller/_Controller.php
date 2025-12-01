<?php

namespace app\controller;

use think\App;
use think\Response;

class _Controller
{
    protected $app;
    protected $request;
    protected $curUser;
    protected $reqPId;
    protected $reqId;

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 获取请求ID
        $this->reqId = $this->request->param('id', 0);

        if (session('?user')) {
            $this->curUser = session('user');
        }

        if (!($this->reqPId = $this->request->post('project_id'))) {
            $this->reqPId = $this->request->get('project_id');
        }
    }

    /**
     * 成功响应
     */
    protected function success(string $msg = '', $data = null): Response
    {
        return json([
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 失败响应
     */
    protected function fail(string $msg = '', $data = null): Response
    {
        return json([
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 视图渲染
     */
    protected function view(string $template = '', array $vars = []): Response
    {
        return view($template, $vars);
    }

    public function vueSuccess($msg = '', $data = null)
    {
        if (is_object($msg)) {
            $msg = $msg->toArray();
        }
        if (is_object($data)) {
            $data = $data->toArray();
        }

        if (is_array($msg)) {
            $data = $msg;
            $msg  = '';
        }

        return $this->success($msg, $data);
    }

    /**
     * 构建请求参数
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function buildTableParames($excludeFields = [])
    {
        $param   = $this->request->param();
        $page    = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;
        $limit   = isset($param['limit']) && !empty($param['limit']) ? $param['limit'] : 14;
        $filters = isset($param['filter']) && !empty($param['filter']) ? $param['filter'] : '{}';
        $ops     = isset($param['op']) && !empty($param['op']) ? $param['op'] : '{}';

        // json转数组
        $filters = json_decode($filters, true);
        $ops     = json_decode($ops, true);
        $where   = $excludes = [];

        $order = 'id desc';
        if (!empty($param['sort'])) {
            $order = $param['sort'] . ' ' . $param['order'] . ',id desc';
        }

        foreach ($filters as $key => $val) {
            if (in_array($key, $excludeFields)) {
                $excludes[$key] = $val;
                continue;
            }

            $op = isset($ops[$key]) && !empty($ops[$key]) ? $ops[$key] : 'LIKE';

            switch (strtolower($op)) {
                case '=':
                    $where[] = [$key, '=', str_replace('id-', '', $val)];
                    break;
                case 'like':
                    $where[] = [$key, 'LIKE', "%{$val}%"];
                    break;
                case 'between':
                    if ($key == 'created_at' || $key == 'updated_at') {
                        [$beginTime, $endTime] = explode(',', $val);
                        $val = date('Y-m-d H:i:s', $beginTime) . ',' . date('Y-m-d H:i:s', $endTime);
                    }
                    $where[] = [$key, 'BETWEEN', $val];
                    break;
                default:
                    $where[] = [$key, 'LIKE', "%{$val}%"];
            }
        }

        return [$where, $page, $limit, $order, $excludes];
    }
}
