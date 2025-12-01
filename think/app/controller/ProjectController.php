<?php

namespace app\controller;

use app\model\Issue;
use app\model\Project;
use app\model\User;
use think\db\Query;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class ProjectController extends _Controller
{
    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function listAction()
    {
        $IssueCount     = [
            'issueList' => function (Query $query, &$alias) {
                $query->where('status', '<>', 8);
                $alias = 'issueCount';
            },
        ];
        $UserIssueCount = [
            'issueList' => function (Query $query, &$alias) {
                $query->where([
                    [
                        'status',
                        '<>',
                        8,
                    ],
                    [
                        'cur_user_id',
                        '=',
                        $this->curUser['id'],
                    ],
                ]);
                $alias = 'userIssueCount';
            },
        ];

        $where[] = [
            'status',
            '=',
            $this->request->get('status'),
        ];

        $data['projectList'] = Project::filterWhere($where)
            ->withCount($IssueCount)
            ->withCount($UserIssueCount)
            ->select();

        if ($this->curUser['role_id'] == 2) {
            $data['projectList'] = Project::hasWhere('userProjectList', function (Query $query) {
                $query->where(['user_id' => $this->curUser['id']]);
            })
                ->withCount($IssueCount)
                ->withCount($UserIssueCount)
                ->select();
        }

        $data['user'] = User::filterWhere([
            'status' => 1,
            'id'     => $this->curUser['id'],
        ])
            ->withCount($UserIssueCount)
            ->find();

        return $this->vueSuccess($data);
    }

    public function editAction()
    {
        $row = Project::findOrNew($this->reqId);

        if ($post = $this->request->post()) {
            $modules = $post['modules'] ?? [];
            unset($post['modules']);
            
            $row->save($post);

            // 创建模块
            if (!empty($modules) && is_array($modules)) {
                foreach ($modules as $moduleName) {
                    if (!empty(trim($moduleName))) {
                        \app\model\Module::create([
                            'project_id' => $row->id,
                            'name' => trim($moduleName),
                            'status' => 1,
                        ]);
                    }
                }
            }

            return $this->vueSuccess('添加成功', $row);
        }
    }

    public function editStatusAction()
    {
        if ($post = $this->request->post()) {
            $row = (new Project())->where(['id' => $post['id']])
                ->save(['status' => $post['status']]);

            return $this->vueSuccess('操作成功', $row);
        }
    }
}
