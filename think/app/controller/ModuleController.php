<?php

namespace app\controller;

use app\model\Module;
use app\model\Project;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class ModuleController extends _Controller
{
    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function listAction()
    {
        $list               = Project::filterWhere(['id' => $this->reqId])
            ->with(['moduleList'])
            ->find();
        $list['moduleList'] = getChildren($list['moduleList']);

        return $this->vueSuccess($list);
    }

    public function updateAllAction()
    {
        $row = (new Module());

        if ($post = $this->request->post()) {

            $data = [];
            foreach ($post['list'] as $k => $v) {
                if (empty($v['id'])) {
                    unset($v['id']);
                }
                if (!empty($v['children'])) {
                    foreach ($v['children'] as $v1) {
                        if (empty($v1['id'])) {
                            unset($v1['id']);
                        }
                        if (!empty($v1['children'])) {
                            foreach ($v1['children'] as $v2) {
                                if (empty($v2['id'])) {
                                    unset($v2['id']);
                                }
                                $data[] = $v2;
                            }
                        }
                        $data[] = $v1;
                    }
                }
                $data[] = $v;
            }

            $row->saveAll($data);

            return $this->vueSuccess();
        }

        return $this->vueSuccess($row);
    }

    public function delAction()
    {
        if ($post = $this->request->post()) {
            $row = Module::whereIn('id', $post['id'])
                ->select();

            try {
                foreach ($row as $v) {
                    $this->deleteWithChildren($v);
                }
            } catch (\Exception $e) {
                return $this->fail('删除失败');
            }
        }
    }

    /**
     * 递归删除模块及其子模块
     * @param $module
     */
    private function deleteWithChildren($module)
    {
        if (!$module->children->isEmpty()) {
            foreach ($module->children as $child) {
                $this->deleteWithChildren($child);
            }
        }
        $module->delete();
    }
}
