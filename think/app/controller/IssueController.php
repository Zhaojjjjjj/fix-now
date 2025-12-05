<?php

namespace app\controller;

use app\model\Issue;
use app\model\IssueLog;
use app\model\Module;
use app\model\Project;
use app\model\User;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class IssueController extends _Controller
{
    public function listAction()
    {
        [$where, $page, $limit, $order] = $this->buildTableParames();

        $order = 'status asc,priority desc,id desc';

        $priority    = $this->request->get('priority');
        $status      = $this->request->get('status');
        $title       = $this->request->get('title');
        $environment = $this->request->get('environment');
        $bugType     = $this->request->get('bug_type');
        $searchIssue = $this->request->get('searchIssue');

        // New filters
        $curUserId = $this->request->get('cur_user_id');
        $userId    = $this->request->get('user_id');

        $where[] = ['priority', '=', $priority];
        $where[] = ['status', '=', $status];
        $where[] = ['title', 'LIKE', '%' . $title . '%'];
        $where[] = ['project_id', '=', $this->reqPId];
        
        if (!empty($curUserId)) {
            $where[] = ['cur_user_id', '=', $curUserId];
        }
        if (!empty($userId)) {
            $where[] = ['user_id', '=', $userId];
        }
        
        // 新增环境筛选
        if (!empty($environment)) {
            $where[] = ['environment', '=', $environment];
        }
        
        // 新增bug类型筛选
        if (!empty($bugType)) {
            $where[] = ['bug_type', '=', $bugType];
        }

        if ($searchIssue == 1) {
            $where[] = ['cur_user_id', '=', session('user')['id']];
            $where[] = ['status', '<>', 8];
        } else if ($searchIssue == 2) {
            $where[] = ['status', '<>', 8];
        }

        // 用户权限
        if ($this->curUser['role_id'] == 2) {
            $where[] = ['user_id', '=', $this->curUser['id']];
        }

        $data = (new Issue())->findList(['user', 'curUser', 'module'], $where, $page, $limit, $order);

        return $this->vueSuccess($data);
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function editAction()
    {
        /* @var Issue $row */
        $row = Issue::findOrNew($this->reqId);

        if ($post = $this->request->post()) {
            $data            = $post;
            // 只在创建新issue时设置user_id和默认状态
            if ($row->isEmpty()) {
                $data['user_id'] = $this->curUser['id'];
                $data['status']  = Issue::STATUS_NOTSOLVE;
            }
            $row->save($data);

            if (!empty($post['content'])) {
                $row->editLog($this->curUser['id'], $data['type'] ?? 1, $post['content']);
            }

            return $this->vueSuccess('操作成功', $row);
        }

        if ($row->isEmpty()) {
            $row->project = Project::findOne($this->reqPId);
        } else {
            $row->project;
        }

        $row['userList']   = User::filterWhere(['status' => 1])
            ->withJoin('userProjectList')
            ->select();
        $projectId = $row['project_id'] ?: $this->reqPId;
        $row['moduleList'] = getChildren(Module::filterWhere(['status' => 1, 'project_id' => $projectId])
            ->select());

        return $this->vueSuccess($row);
    }

    /**
     * 日志列表
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function logListAction()
    {
        if (empty($this->reqId)) {
            return $this->fail('参数错误');
        }

        $order = IssueLog::filterWhere(['status' => 1, 'issue_id' => $this->reqId])
            ->with(['user', 'nextUser'])
            ->order('created_at asc')
            ->select()
            ->toArray();

        return $this->vueSuccess($order);
    }

    /**
     * 日志处理
     */
    public function dealAction()
    {
        $row = Issue::findOne($this->reqId);

        if ($post = $this->request->post()) {
            switch ($post['type']) {
                /** 2 修复 */
                case IssueLog::TYPE_FIX:
                    $row->status      = Issue::STATUS_AUDIT;
                    $row->cur_user_id = $row->user_id;
                    $row->save();

                    $row->log($this->curUser['id'], IssueLog::TYPE_FIX, $post['content']);
                    break;
                /** 3 驳回 */
                case IssueLog::TYPE_REJECT:
                    $row->status      = Issue::STATUS_AUDIT;
                    $row->cur_user_id = $row->user_id;
                    $row->save();

                    $row->log($this->curUser['id'], IssueLog::TYPE_REJECT, $post['content']);
                    break;
                /** 4 不通过 */
                case IssueLog::TYPE_VER_REJECT:
                    $lastLog = $row->issueLogList()
                        ->where([
                            'type' => [
                                IssueLog::TYPE_FIX,
                                IssueLog::TYPE_REJECT,
                            ],
                        ])
                        ->order('id DESC')
                        ->find();

                    $row->status      = Issue::STATUS_NOTSOLVE;
                    $row->cur_user_id = $lastLog->user_id;
                    $row->save();

                    $row->log($this->curUser['id'], IssueLog::TYPE_VER_REJECT, $post['content']);
                    break;
                /** 5 评论 */
                case IssueLog::TYPE_COMMENT:
                    $row->log($this->curUser['id'], IssueLog::TYPE_COMMENT, $post['content']);
                    break;
                /** 6 指派 */
                case IssueLog::TYPE_ASSIGN:
                    $row->status      = Issue::STATUS_NOTSOLVE;
                    $row->cur_user_id = $post['cur_user_id'];
                    $row->save();

                    $row->log($this->curUser['id'], IssueLog::TYPE_ASSIGN, $post['content']);
                    break;
                /** 8 通过 */
                case IssueLog::TYPE_FINISH:
                    $row->status      = Issue::STATUS_FINISH;
                    $row->cur_user_id = 0;
                    $row->save();

                    $row->log($this->curUser['id'], IssueLog::TYPE_FINISH, $post['content']);
                    break;
                default:
                    return $this->fail('参数错误');
            }

            return $this->vueSuccess($row);
        }

        return $this->vueSuccess($row);
    }
}
