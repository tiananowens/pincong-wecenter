<?php
/**
 * WeCenter Framework
 *
 * An open source application development framework for PHP 5.2.2 or newer
 *
 * @package     WeCenter Framework
 * @author      WeCenter Dev Team
 * @copyright   Copyright (c) 2011 - 2014, WeCenter, Inc.
 * @license     http://www.wecenter.com/license/
 * @link        http://www.wecenter.com/
 * @since       Version 1.0
 * @filesource
 */

/**
 * WeCenter APP 函数类
 *
 * @package     WeCenter
 * @subpackage  App
 * @category    Model
 * @author      WeCenter Dev Team
 */


if (!defined('IN_ANWSION'))
{
    die;
}

class account_class extends AWS_MODEL
{
    /**
     * 检查用户名是否已经存在
     *
     * @param string
     * @return boolean
     */
    public function check_username($user_name)
    {
    	$user_name = my_trim($user_name);

        return $this->fetch_one('users', 'uid', "user_name = '" . $this->quote($user_name) . "'");
    }

    /**
     * 检查用户名中是否包含敏感词或用户信息保留字
     *
     * @param string
     * @return boolean
     */
    public function check_username_sensitive_words($user_name)
    {
        if (H::sensitive_word_exists($user_name))
        {
            return true;
        }

        if (!get_setting('censoruser'))
        {
            return false;
        }

        if ($censorusers = explode("\n", get_setting('censoruser')))
        {
            foreach ($censorusers as $name)
            {
                if (!$name = trim($name))
                {
                    continue;
                }

                if (preg_match('/(' . $name . ')/is', $user_name))
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 检查用户 ID 是否已经存在
     *
     * @param string
     * @return boolean
     */

    public function check_uid($uid)
    {
		$uid = intval($uid);
		if ($uid <= 0)
		{
			return false;
		}
        return $this->fetch_one('users', 'uid', 'uid = ' . ($uid));
    }


    /**
     * 用户登录验证 (MD5 验证)
     *
     * @param string
     * @param string
     * @return array
     */
    public function check_hash_login($user_name, $password_md5)
    {
        if (!$user_name OR !$password_md5)
        {
            return false;
        }

        $user_info = $this->get_user_info_by_username($user_name);

        if (! $user_info)
        {
            return false;
        }

        if (!password_verify($password_md5, $user_info['password']))
        {
            return false;
        }
        else
        {
            return $user_info;
        }

    }

    /**
     * 用户密码验证
     *
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    public function check_password($password, $db_password, $salt)
    {
        $password = compile_password($password, $salt);

        if (password_verify($password, $db_password))
        {
            return true;
        }

        return false;

    }

    /**
     * 通过用户名获取用户信息
     *
     * $attrb 为是否获取附加表信息, $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_user_info_by_username($user_name, $attrb = false, $cache_result = true)
    {
        if ($uid = $this->fetch_one('users', 'uid', "user_name = '" . $this->quote($user_name) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    /**
     * 通过 UID 获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_user_info_by_uid($uid, $attrib = false, $cache_result = true)
    {
        $uid = intval($uid);
        if ($uid <= 0)
        {
            return false;
        }

        if ($cache_result)
        {
            static $users_info;

            if ($users_info[$uid . '_attrib'])
            {
                return $users_info[$uid . '_attrib'];
            }
            else if ($users_info[$uid])
            {
                return $users_info[$uid];
            }
        }

        if (! $user_info = $this->fetch_row('users', 'uid = ' . $uid))
        {
            return false;
        }

		if ($attrib)
		{
			if ($user_attrib = $this->fetch_row('users_attrib', 'uid = ' . $uid))
			{
				foreach ($user_attrib AS $key => $val)
				{
					$user_info[$key] = $val;
				}

				if ($user_info['extra_data'])
				{
					$user_info['extra_data'] = unserialize($user_info['extra_data']);
				}

				if (!is_array($user_info['extra_data']))
				{
					$user_info['extra_data'] = array();
				}
			}
		}

        if ($user_info['user_name'])
        {
            $user_info['url_token'] = urlencode($user_info['user_name']);
        }

        $users_info[$uid] = $user_info;

        if ($attrib)
        {
            unset($users_info[$uid]);

            $users_info[$uid . '_attrib'] = $user_info;
        }

        return $user_info;
    }

    /**
     * 通过 UID 数组获取用户信息
     *
     * @param array
     * @param boolean
     * @return array
     */
    public function get_user_info_by_uids($uids, $attrib = false)
    {
        if (! is_array($uids) OR sizeof($uids) == 0)
        {
            return false;
        }

        array_walk_recursive($uids, 'intval_string');

        $uids = array_unique($uids);

        static $users_info;

        if ($users_info[implode('_', $uids) . '_attrib'])
        {
            return $users_info[implode('_', $uids) . '_attrib'];
        }
        else if ($users_info[implode('_', $uids)])
        {
            return $users_info[implode('_', $uids)];
        }

        if ($user_info = $this->fetch_all('users', "uid IN(" . implode(',', $uids) . ")"))
        {
            foreach ($user_info as $key => $val)
            {
                $val['url_token'] = urlencode($val['user_name']);

                unset($val['password'], $val['salt']);

                $data[$val['uid']] = $val;

                $query_uids[] = $val['uid'];
            }

            foreach ($uids AS $uid)
            {
                if ($data[$uid])
                {
                    $result[$uid] = $data[$uid];
                }
            }

            $users_info[implode('_', $uids)] = $data;
        }

        if ($attrib AND $query_uids)
        {
            if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $query_uids) . ')'))
            {
                foreach ($users_attrib AS $key => $val)
                {
                    unset($val['id']);

                    foreach ($val AS $attrib_key => $attrib_val)
                    {
                        $result[$val['uid']][$attrib_key] = $attrib_val;
                    }

					if ($result[$val['uid']]['extra_data'])
					{
						$result[$val['uid']]['extra_data'] = unserialize($result[$val['uid']]['extra_data']);
					}

					if (!is_array($result[$val['uid']]['extra_data']))
					{
						$result[$val['uid']]['extra_data'] = array();
					}
                }
            }

            unset($users_info[implode('_', $uids)]);

            $users_info[implode('_', $uids) . '_attrib'] = $result;
        }

        return $result;
    }

    /**
     * 根据用户ID获取用户通知设置
     * @param $uid
     */
    public function get_notification_setting_by_uid($uid)
    {
        if (!$setting = $this->fetch_row('users_notification_setting', 'uid = ' . intval($uid)))
        {
            return array('data' => array());
        }

        $setting['data'] = unserialize($setting['data']);

        if (!$setting['data'])
        {
            $setting['data'] = array();
        }

        return $setting;
    }

    /**
     * 插入用户数据
     *
     * @param string
     * @param string
     * @param string
     * @param int
     * @param string
     * @return int
     */
    public function insert_user($user_name, $password)
    {
        if (!$user_name OR !$password)
        {
            return false;
        }

        if ($this->check_username($user_name))
        {
            return false;
        }

        $salt = fetch_salt();

        if ($uid = $this->insert('users', array(
            'user_name' => htmlspecialchars($user_name),
            'password' => bcrypt_password_hash(compile_password($password, $salt)),
            'salt' => $salt,
            'sex' => 0,
            'group_id' => 4,
            'avatar_file' => null, // 无头像
            'reg_time' => fake_time()
        )))
        {
            $this->insert('users_attrib', array(
                'uid' => $uid
            ));

            $this->update_notification_setting_fields(get_setting('new_user_notification_setting'), $uid);

            //$this->model('search_fulltext')->push_index('user', $user_name, $uid);
        }

        return $uid;
    }

    /**
     * 注册用户
     *
     * @param string
     * @param string
     * @param string
     * @return int
     */
    public function user_register($user_name, $password = null)
    {
        if ($uid = $this->insert_user($user_name, $password))
        {
            if ($def_focus_uids_str = get_setting('def_focus_uids'))
            {
                $def_focus_uids = explode(',', $def_focus_uids_str);

                foreach ($def_focus_uids as $key => $val)
                {
                    $this->model('follow')->user_follow_add($uid, $val);
                }
            }

            $this->model('currency')->process($uid, 'REGISTER', get_setting('currency_system_config_register'), '初始资本');
        }

        return $uid;
    }

    /**
     * 发送欢迎信息
     *
     * @param int
     * @param string
     */
    public function welcome_message($uid, $user_name)
    {
        if (get_setting('welcome_message_pm'))
        {
            $this->model('message')->send_message($uid, $uid, str_replace(array('{username}', '{time}', '{sitename}'), array($user_name, date('Y-m-d H:i:s', time()), get_setting('site_name')), get_setting('welcome_message_pm')));
        }
    }

    /**
     * 更新用户表字段
     *
     * @param array
     * @param uid
     * @return int
     */
    public function update_users_fields($update_data, $uid)
    {
        return $this->update('users', $update_data, 'uid = ' . intval($uid));
    }

    /**
     * 更新用户名
     *
     * @param string
     * @param uid
     */
    public function update_user_name($user_name, $uid)
    {
        $this->update('users', array(
            'user_name' => htmlspecialchars($user_name),
            'user_update_time' => fake_time()
        ), 'uid = ' . intval($uid));

        //return $this->model('search_fulltext')->push_index('user', $user_name, $uid);

        return true;
    }

    /**
     * 更新用户附加表状态或字段
     *
     * @param array
     * @param uid
     * @return boolean
     */
    public function update_users_attrib_fields($update_data, $uid)
    {
		if (isset($update_data['extra_data']) AND $update_data['extra_data'] !== null)
		{
			if (is_array($update_data['extra_data']))
			{
				if (count($update_data['extra_data']) > 0)
				{
					$update_data['extra_data'] = serialize($update_data['extra_data']);
				}
				else
				{
					// 如果是空数组就直接设为 null
					$update_data['extra_data'] = null;
				}
			}
			else
			{
				unset($update_data['extra_data']);
			}
		}

		if (!$this->update('users_attrib', $update_data, 'uid = ' . intval($uid)))
		{
			// $this->update 并不能用来检查 users_attrib 是否存在, 如果存在但是未被更新也会返回 0
			if (!$this->fetch_one('users_attrib', 'uid', 'uid = ' . intval($uid)))
			{
				if ($this->check_uid($uid))
				{
					$update_data['uid'] = intval($uid);
					$this->insert('users_attrib', $update_data);
					return true;
				}
				return false;
			}
			return true;
		}
		return true;
    }

    /**
     * 获取用户附加表 extra_data 字段
     *
     * @param array
     * @param uid
     * @return mixed	成功返回 array, 失败返回 false
     */
    public function get_users_attrib_extra_data($uid)
    {
		$user_attrib = $this->fetch_row('users_attrib', 'uid = ' . intval($uid));
		if (!$user_attrib)
		{
			if ($this->check_uid($uid))
			{
				return array();
			}
			// uid 不存在
			return false;
		}

		if ($user_attrib['extra_data'])
		{
			$result = unserialize($user_attrib['extra_data']);
		}

		if (!is_array($result))
		{
			$result = array();
		}

		return $result;
    }

    /**
     * 更新用户附加表 extra_data 字段
     *
     * @param array
     * @param uid
     * @return void
     */
    public function update_users_attrib_extra_data($data, $uid)
    {
		if (!is_array($data))
		{
			return;
		}

		$extra_data = $this->get_users_attrib_extra_data($uid);
		if (!is_array($extra_data))
		{
			return;
		}

		// 覆盖或刪除原有的
		foreach ($data AS $key => $val)
		{
			if ($val === null)
			{
				unset($extra_data[$key]);
			}
			else
			{
				$extra_data[$key] = $val;
			}
		}

		$this->update_users_attrib_fields(array('extra_data' => $extra_data), $uid);
    }

    /**
     * 更改用户密码
     *
     * @param  string
     * @param  string
     * @param  int
     * @param  string
     */
    public function update_user_password($oldpassword, $password, $uid, $salt)
    {
        if (!$salt OR !$uid)
        {
            return false;
        }

        $oldpassword = compile_password($oldpassword, $salt);

        if (! $user_info = $this->fetch_row('users', 'uid = ' . intval($uid)))
        {
            return false;
        }

        if (!password_verify($oldpassword, $user_info['password']))
        {
            return false;
        }

        return $this->update_user_password_ingore_oldpassword($password, $uid, $salt);
    }

    /**
     * 更改用户不用旧密码密码
     *
     * @param  string
     * @param  int
     * @param  string
     */
    public function update_user_password_ingore_oldpassword($password, $uid, $salt)
    {
        if (!$salt OR !$password OR !$uid)
        {
            return false;
        }

        $this->update('users', array(
            'password' => bcrypt_password_hash(compile_password($password, $salt)),
            'salt' => $salt
        ), 'uid = ' . intval($uid));

        return true;
    }

    /**
     * 更新用户最后登录时间
     *
     * @param  int
     */
    public function update_user_last_login($uid)
    {
        if (! $uid)
        {
            return false;
        }

        return $this->shutdown_update('users', array(
            'last_login' => fake_time()
        ), 'uid = ' . intval($uid));
    }

    /**
     * 更新用户通知设置
     *
     * @param  array
     * @param  int
     * @return boolean
     */
    public function update_notification_setting_fields($data, $uid)
    {
        if (!$this->count('users_notification_setting', 'uid = ' . intval($uid)))
        {
            $this->insert('users_notification_setting', array(
                'data' => serialize($data),
                'uid' => intval($uid)
            ));
        }
        else
        {
            $this->update('users_notification_setting', array(
                'data' => serialize($data)
            ), 'uid = ' . intval($uid));
        }

        return true;
    }

    public function update_notification_unread($uid)
    {
        return $this->shutdown_update('users', array(
            'notification_unread' => $this->count('notification', 'read_flag = 0 AND recipient_uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function update_question_invite_count($uid)
    {
        return $this->update('users', array(
            'invite_count' => $this->count('question_invite', 'recipients_uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function update_inbox_unread($uid)
    {
        return $this->shutdown_update('users', array(
            'inbox_unread' => ($this->sum('inbox_dialog', 'sender_unread', 'sender_uid = ' . intval($uid)) + $this->sum('inbox_dialog', 'recipient_unread', 'recipient_uid = ' . intval($uid)))
        ), 'uid = ' . intval($uid));
    }


    public function setcookie_login($uid, $user_name, $password, $salt, $expire = null)
    {
        if (! $uid)
        {
            return false;
        }

        if (! $expire)
        {
            HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid));
        }
        else
        {
            HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid), (time() + $expire));
        }

        return true;
    }

    public function setcookie_logout()
    {
        HTTP::set_cookie('_user_login', '', time() - 3600);
    }

    public function logout()
    {
        $this->setcookie_logout();
        $this->setsession_logout();
    }

    public function setsession_logout()
    {
        if (isset(AWS_APP::session()->client_info))
        {
            unset(AWS_APP::session()->client_info);
        }

        if (isset(AWS_APP::session()->permission))
        {
            unset(AWS_APP::session()->permission);
        }
    }

    public function check_username_char($user_name)
    {
        if (is_digits($user_name))
        {
            return AWS_APP::lang()->_t('用户名不能为纯数字');
        }

        if (strstr($user_name, '-') OR strstr($user_name, '.') OR strstr($user_name, '/') OR strstr($user_name, '%') OR strstr($user_name, '__'))
        {
            return AWS_APP::lang()->_t('用户名不能包含 - / . % 与连续的下划线');
        }

        $length = strlen(convert_encoding($user_name, 'UTF-8', 'GB2312'));

        $length_min = intval(get_setting('username_length_min'));
        $length_max = intval(get_setting('username_length_max'));

        if ($length < $length_min || $length > $length_max)
        {
            $flag = true;
        }

        switch(get_setting('username_rule'))
        {
            default:

            break;

            case 1:
                if (!preg_match('/^[\x{4e00}-\x{9fa5}_a-zA-Z0-9]+$/u', $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入大于 %s 字节的用户名, 允许汉字、字母与数字', ($length_min . ' - ' . $length_max));
                }
            break;

            case 2:
                if (!preg_match("/^[a-zA-Z0-9_]+$/i", $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入 %s 个字母、数字或下划线', ($length_min . ' - ' . $length_max));
                }
            break;

            case 3:
                if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入 %s 个汉字', (ceil($length_min / 2) . ' - ' . floor($length_max / 2)));
                }
            break;
        }

        return false;
    }

    public function get_users_list($where = null, $limit = 10, $attrib = false, $exclude_self = true, $orderby = 'uid DESC')
    {
        if ($exclude_self)
        {
            if ($where)
            {
                $where = '(' . $where . ') AND uid <> ' . AWS_APP::user()->get_info('uid');
            }
            else
            {
                $where = 'uid <> ' . AWS_APP::user()->get_info('uid');
            }
        }

        $result = $this->fetch_all('users', $where, $orderby, $limit);

        if ($result)
        {
            foreach ($result AS $key => $val)
            {
            	unset($val['password'], $val['salt']);

                $data[$val['uid']] = $val;

                if ($val['user_name'])
                {
                    $data[$val['uid']]['url_token'] = urlencode($val['user_name']);
                }

                $uids[] = $val['uid'];
            }

            if ($attrib AND $uids)
            {
                if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $uids) . ')'))
                {
                    foreach ($users_attrib AS $key => $val)
                    {
                        unset($val['id']);

                        foreach ($val AS $attrib_key => $attrib_val)
                        {
                            $data[$val['uid']][$attrib_key] = $attrib_val;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 根据 WHERE 条件获取用户数量
     *
     * @param string
     * @return int
     */
    public function get_user_count($where = null)
    {
        return $this->count('users', $where);
    }

    /**
     * 获取头像地址
     *
     * 举个例子：$uid=12345，那么头像路径很可能(根据您部署的上传文件夹而定)会被存储为/uploads/000/01/23/45_avatar_min.jpg
     *
     * @param  int
     * @param  string
     * @param  int
     * @return string
     */
    public function get_avatar($uid, $size = 'min', $return_type = 0)
    {
        $size = in_array($size, array(
            'max',
            'mid',
            'min',
            '50',
            '150',
            'big',
            'middle',
            'small'
        )) ? $size : 'real';

        $uid = abs(intval($uid));
        $uid = sprintf('%\'09d', $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);

        if ($return_type == 1)
        {
            return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
        }

        if ($return_type == 2)
        {
            return substr($uid, -2) . '_avatar_' . $size . '.jpg';
        }

        return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . '_avatar_' . $size . '.jpg';
    }

    /**
     * 删除用户头像
     *
     * @param int
     * @return boolean
     */
    public function delete_avatar($uid)
    {
        if (!$uid)
        {
            return false;
        }

        foreach(AWS_APP::config()->get('image')->avatar_thumbnail as $key => $val)
        {
            @unlink(get_setting('upload_dir') . '/avatar/' . $this->get_avatar($uid, $key, 1) . $this->get_avatar($uid, $key, 2));
        }

        return $this->update_users_fields(array('avatar_file' => null), $uid);
    }

    public function add_user_group($group_name, $group_type, $reputation_lower = 0, $reputation_higer = 0, $reputation_factor = 0)
    {
		if ($group_type == 'member')
		{
			$type = 1;
			$custom = 0;
		}
		elseif ($group_type == 'custom')
		{
			$type = 0;
			$custom = 1;
		}
		else
		{
			return false;
		}
        return $this->insert('users_group', array(
            'type' => $type,
            'custom' => $custom,
            'group_name' => $group_name,
            'reputation_lower' => $reputation_lower,
            'reputation_higer' => $reputation_higer,
            'reputation_factor' => $reputation_factor,
        ));
    }

    public function delete_user_group_by_id($group_id)
    {
        $this->update('users', array(
            'group_id' => 4,
        ), 'group_id = ' . intval($group_id));

        return $this->delete('users_group', 'group_id = ' . intval($group_id));
    }

    public function update_user_group_data($group_id, $data)
    {
        return $this->update('users_group', $data, 'group_id = ' . intval($group_id));
    }

    public function get_user_group_by_id($group_id, $field = null)
    {
        if (!$group_id)
        {
            return false;
        }

        static $user_groups;

        if (isset($user_groups[$group_id]))
        {
            if ($field)
            {
                return $user_groups[$group_id][$field];
            }
            else
            {
                return $user_groups[$group_id];
            }
        }

        if (!$user_group = AWS_APP::cache()->get('user_group_' . intval($group_id)))
        {
            $user_group = $this->fetch_row('users_group', 'group_id = ' . intval($group_id));

            if ($user_group['permission'])
            {
                $user_group['permission'] = unserialize($user_group['permission']);
            }

            AWS_APP::cache()->set('user_group_' . intval($group_id), $user_group, get_setting('cache_level_normal'), 'users_group');
        }

        $user_groups[$group_id] = $user_group;

        if ($field)
        {
            return $user_group[$field];
        }
        else
        {
            return $user_group;
        }
    }

	// $type 0:系统组|1:会员组(威望组)
    public function get_user_group_list($type = 0, $custom = null)
    {
        $type = intval($type);

        $where[] = 'type = ' . $type;

        if (isset($custom))
        {
            $where[] = 'custom = ' . intval($custom);
        }

        if ($users_groups = $this->fetch_all('users_group', implode(' AND ', $where), 'reputation_lower ASC'))
        {
            foreach ($users_groups as $key => $val)
            {
                $group[$val['group_id']] = $val;
            }
        }

        return $group;
    }

    public function get_user_group($group_id, $reputation_group = 0)
    {
        if ($group_id == 4 AND $reputation_group)
        {
            if ($user_group = $this->model('account')->get_user_group_by_id($reputation_group))
            {
                return $user_group;
            }
        }

        return $this->model('account')->get_user_group_by_id($group_id);
    }

	public function get_user_group_by_user_info(&$user_info)
	{
		return $this->model('account')->get_user_group(
			$user_info['group_id'],
			$this->model('reputation')->get_reputation_group_id_by_reputation($user_info['reputation'])
		);
	}

    public function forbid_user_by_uid($uid, $status, $admin_uid = null, $reason = null)
    {
		if (!$uid)
		{
			return false;
		}

		$status = intval($status);
		if (!$this->update_users_fields(array(
			'forbidden' => ($status),
			'user_update_time' => fake_time()
		), $uid))
		{
			return false;
		}

		if (!$status)
		{
			$extra_data = array(
				'banned_by' => null,
				'banned_reason' => null
			);
		}
		else
		{
			if ($reason)
			{
				$reason = htmlspecialchars($reason);
			}
			$extra_data = array(
				'banned_by' => $admin_uid,
				'banned_reason' => $reason
			);
		}

		$this->update_users_attrib_extra_data($extra_data, $uid);
    }

    public function set_default_timezone($time_zone, $uid)
    {
        return $this->update('users', array(
            'default_timezone' => htmlspecialchars($time_zone)
        ), 'uid = ' . intval($uid));
    }

    public function save_recent_topics($uid, $topic_title)
    {
        if (!$user_info = $this->get_user_info_by_uid($uid))
        {
            return false;
        }

        if ($user_info['recent_topics'])
        {
            $recent_topics = unserialize($user_info['recent_topics']);
        }

        $new_recent_topics[0] = $topic_title;

        if ($recent_topics)
        {
            foreach ($recent_topics AS $key => $val)
            {
                if ($val != $topic_title)
                {
                    $new_recent_topics[] = $val;
                }
            }
        }

        if (count($new_recent_topics) > 10)
        {
            $new_recent_topics = array_slice($new_recent_topics, 0, 10);
        }

        return $this->update('users', array(
            'recent_topics' => serialize($new_recent_topics)
        ), 'uid = ' . intval($uid));
    }

	public function calc_user_recovery_code($uid)
	{
		if (! $user_info = $this->fetch_row('users', 'uid = ' . intval($uid)))
		{
			return false;
		}
		return md5($user_info['user_name'] . $user_info['uid'] . md5($user_info['password'] . $user_info['salt']) . G_COOKIE_HASH_KEY);
	}

	public function verify_user_recovery_code($uid, $recovery_code)
	{
		if (!$code = $this->calc_user_recovery_code($uid))
		{
			return false;
		}
		return ($code == $recovery_code);
	}

}
