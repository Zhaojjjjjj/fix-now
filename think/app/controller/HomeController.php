<?php

namespace app\controller;

use app\model\Module;
use app\model\Project;
use app\model\UserProject;
use Carbon\Carbon;
use think\facade\Db;

class HomeController extends _Controller
{
    public function indexAction()
    {
        return $this->view('index', ['layout' => false]);
    }

    public function editAction()
    {
        if ($post = $this->request->post()) {
            $project     = Project::findOrNew($this->reqId);
            $userProject = UserProject::findOrNew($this->reqId);

            Db::startTrans();
            try {
                $project->save($post);
                $userProject->save([
                    'user_id'    => $this->curUser['id'],
                    'project_id' => $project['id'],
                ]);

                Db::commit();
            } catch (\Exception $e) {

                Db::rollback();
            }

            if ($post['add_module_name']) {
                $module = new Module();
                $module->save([
                    'project_id' => $project['id'],
                    'name'       => $post['add_module_name'],
                ]);
            }

            return $this->vueSuccess('成功');
        }

        return $this->fail('非法操作');
    }

}
