<?php

function sortByName($a,$b)
{
    $a_str = strtolower($a['name']);
    $b_str = strtolower($b['name']);
    if ($a_str == $b_str) {
        return 0;
    }
    return ($a_str < $b_str) ? -1 : 1;
}

class Dase_Handler_Admin extends Dase_Handler
{
    public $resource_map = array(
        '/' => 'admin',
        'set' => 'set',
        'user/email' => 'user_email',
        'directory' => 'directory',
        'users' => 'users',
        'add_user_form/{eid}' => 'add_user_form',
        'user/{id}/is_admin' => 'is_admin',
        'create' => 'content_form',
    );

    protected function setup($r)
    {
        $this->user = $r->getUser();
        if ($this->user->is_admin) {
            //ok
        } else {
            $r->renderError(401);
        }
    }

    public function getDirectory($r) 
    {
        if ($r->get('lastname')) {
            $results = Utlookup::lookup($r->get('lastname'),'sn');
            usort($results,'sortByName');
            $r->assign('lastname',$r->get('lastname'));
            $r->assign('results',$results);
        }
        $r->renderTemplate('framework/admin_directory.tpl');
    }

    public function postToUserEmail($r)
    {
        $this->user->email = $r->get('email');
        $this->user->update();
        $r->renderRedirect('admin');
    }

    public function getAdmin($r) 
    {
        $r->renderTemplate('framework/admin.tpl');
    }

    public function getUsers($r) 
    {
        $users = new Dase_DBO_User($this->db);
        $users->orderBy('name');
        $r->assign('users', $users->findAll(1));
        $r->renderTemplate('framework/admin_users.tpl');
    }

    public function getAddUserForm($r) 
    {
        $record = Utlookup::getRecord($r->get('eid'));
        $u = new Dase_DBO_User($this->db);
        $u->eid = $r->get('eid');
        if ($u->findOne()) {
            $r->tpl->assign('user',$u);
        }
        $r->assign('record',$record);
        $r->renderTemplate('framework/admin_add_user.tpl');
    }

    public function postToUsers($r)
    {
        $record = Utlookup::getRecord($r->get('eid'));
        $user = new Dase_DBO_User($this->db);
        $user->eid = $record['eid'];
        if (!$user->findOne()) {
            $user->name = $record['name'];
            $user->email = $record['email'];
            $user->insert();
        } else {
            //$user->update();
        }
        $r->renderRedirect('admin/users');

    }

    public function deleteIsAdmin($r) 
    {
        $user = new Dase_DBO_User($this->db);
        $user->load($r->get('id'));
        $user->is_admin = 0;
        $user->update();
        $r->renderResponse('deleted privileges');
    }

    public function putIsAdmin($r) 
    {
        $user = new Dase_DBO_User($this->db);
        $user->load($r->get('id'));
        $user->is_admin = 1;
        $user->update();
        $r->renderResponse('added privileges');
    }

    private function _findNextUnique($base_dir,$basename,$ext,$iter=0)
    {
        if ($iter) {
            $checkname = $basename.'_'.$iter.'.'.$ext;
        } else {
            $checkname = $basename.'.'.$ext;
        }
        if (!file_exists($base_dir.'/'.$checkname)) {
            return $checkname;
        } else {
            $iter++;
            return $this->_findNextUnique($base_dir,$basename,$ext,$iter);
        }

    }

    private function _findUniqueName($name,$iter=0)
    {
        if ($iter) {
            $checkname = $name.'_'.$iter;
        } else {
            $checkname = $name;
        }
        $item = new Dase_DBO_Item($this->db);
        $item->name = $checkname;
        if (!$item->findOne()) {
            return $checkname;
        } else {
            $iter++;
            return $this->_findUniqueName($name,$iter);
        }
    }
}

