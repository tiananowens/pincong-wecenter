<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class category_class extends AWS_MODEL
{
	public function fetch_category()
	{
		$category_list = array();

		$order = 'sort DESC, id ASC';

		if ($category_all = $this->fetch_all('category', '', $order))
		{
			foreach ($category_all AS $key => $val)
			{
				$category_list[$val['id']] = $val;
			}
		}

		return $category_list;
	}

	public function get_category_list()
	{
		static $category_list;

		if (!$category_list)
		{
			$category_list = AWS_APP::cache()->get('category_list');

			if (!$category_list)
			{
				$category_list = $this->fetch_category();

				AWS_APP::cache()->set('category_list', $category_list, get_setting('cache_level_low'), 'category');
			}
		}

		return $category_list;
	}

	public function get_category_info($category_id)
	{
		static $category_list;

		if (!$category_list)
		{
			$category_list = $this->get_category_list();
		}

		return $category_list[$category_id];
	}

	public function update_category_info($category_id, $title, $group_id, $description = null)
	{
		AWS_APP::cache()->cleanGroup('category');

		return $this->update('category', array(
			'title' => htmlspecialchars($title),
			'group_id' => intval($group_id),
			'description' => $description
		), 'id = ' . intval($category_id));
	}

	public function set_category_sort($category_id, $sort)
	{
		AWS_APP::cache()->cleanGroup('category');

		return $this->update('category', array(
			'sort' => intval($sort)
		), 'id = ' . intval($category_id));
	}

	public function add_category($title, $group_id)
	{
		AWS_APP::cache()->cleanGroup('category');

		return $this->insert('category', array(
			'title' => $title,
			'group_id' => intval($group_id)
		));
	}

	public function delete_category($category_id)
	{
		AWS_APP::cache()->cleanGroup('category');

		return $this->delete('category', 'id = ' . intval($category_id));
	}

	public function category_exists($category_id)
	{
		return $this->count('category', "id = " . intval($category_id));
	}

	public function contents_exists($category_id)
	{
		if ($this->fetch_one('question', 'question_id', 'category_id = ' . intval($category_id)))
		{
			return true;
		}
		if ($this->fetch_one('article', 'id', 'category_id = ' . intval($category_id)))
		{
			return true;
		}
		if ($this->fetch_one('video', 'id', 'category_id = ' . intval($category_id)))
		{
			return true;
		}
	}

	public function move_contents($from_id, $target_id)
	{
		if (!$from_id OR !$target_id)
		{
			return false;
		}

		$this->update('question', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));

		$this->update('article', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));

		$this->update('video', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));

		$this->update('posts_index', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));
	}
}
