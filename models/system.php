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

class system_class extends AWS_MODEL
{
	public function check_stop_keyword($keyword)
	{
		$keyword = trim($keyword);

		if ($keyword == '')
		{
			return false;
		}

		if (cjk_strlen($keyword) == 1)
		{
			return false;
		}

		if (strstr($keyword, '了') OR strstr($keyword, '的') OR strstr($keyword, '有'))
		{
			return false;
		}

		$stop_words_list = array(
			'末', '啊', '阿', '哎', '哎呀', '哎哟', '唉', '俺',
			'俺们', '按', '按照', '吧', '吧哒', '把', '被', '本',
			'本着', '比', '比方', '比如', '鄙人', '彼', '彼此', '边',
			'别', '别说', '并', '并且', '不比', '不成', '不单', '不但',
			'不独', '不管', '不光', '不过', '不仅', '不拘', '不论', '不怕',
			'不然', '不如', '不特', '不惟', '不问', '不只', '朝', '朝着',
			'趁', '趁着', '乘', '冲', '除', '除此之外', '除非', '此',
			'此间', '此外', '从', '从而', '打', '待', '但', '但是',
			'当', '当着', '到', '得', '等', '等等', '地', '第',
			'叮咚', '对', '对于', '多', '多少', '而', '而况', '而且',
			'而是', '而外', '而言', '而已', '尔后', '反过来', '反过来说',
			'反之', '非但', '非徒', '否则', '嘎', '嘎登', '该', '赶', '个',
			'各', '各个', '各位', '各种', '各自', '给', '根据', '跟', '故',
			'故此', '固然', '关于', '管', '归', '果然', '果真', '过', '哈',
			'哈哈', '呵', '和', '何', '何处', '何况', '何时', '嘿', '哼', '哼唷',
			'呼哧', '乎', '哗', '还是', '换句话说', '换言之', '或', '或是', '或者',
			'及', '及其', '及至', '即', '即便', '即或', '即令', '即若', '即使', '几',
			'几时', '己', '既', '既然', '既是', '继而', '加之', '假如', '假若', '假使',
			'鉴于', '将', '较', '较之', '叫', '接着', '结果', '借', '紧接着', '进而',
			'尽', '尽管', '经', '经过', '就', '就是', '就是说', '据', '具体地说',
			'具体说来', '开始', '开外', '靠', '咳', '可', '可见', '可是', '可以',
			'况且', '啦', '来', '来着', '离', '例如', '哩', '连', '连同', '两者',
			'临', '另', '另外', '另一方面', '论', '嘛', '吗', '慢说', '漫说', '冒',
			'么', '每', '每当', '们', '莫若', '某', '某个', '某些', '拿', '哪',
			'哪边', '哪儿', '哪个', '哪里', '哪年', '哪怕', '哪天', '哪些',
			'哪样', '那', '那边', '那儿', '那个', '那会儿', '那里', '那么',
			'那么些', '那么样', '那时', '那些', '那样', '乃', '乃至', '呢',
			'能', '你', '你们', '您', '宁', '宁可', '宁肯', '宁愿', '哦',
			'呕', '啪达', '旁人', '呸', '凭', '凭借', '其', '其次', '其二',
			'其他', '其它', '其一', '其余', '其中', '起', '起见', '起见',
			'岂但', '恰恰相反', '前后', '前者', '且', '然而', '然后', '然则',
			'让', '人家', '任', '任何', '任凭', '如', '如此', '如果', '如何',
			'如其', '如若', '如上所述', '若', '若非', '若是', '啥', '上下',
			'尚且', '设若', '设使', '甚而', '甚么', '甚至', '省得', '时候',
			'什么', '什么样', '使得', '是', '首先', '谁', '谁知', '顺',
			'顺着',  '虽', '虽然', '虽说', '虽则', '随', '随着', '所', '所以',
			'他', '他们', '他人', '它', '它们', '她', '她们', '倘', '倘或', '倘然',
			'倘若', '倘使', '腾', '替', '通过', '同', '同时', '哇', '万一', '往',
			'望', '为', '为何', '为什么', '为着', '喂', '嗡嗡', '我', '我们', '呜',
			'呜呼', '乌乎', '无论', '无宁', '毋宁', '嘻', '吓', '相对而言', '像',
			'向', '向着', '嘘', '呀', '焉', '沿', '沿着', '要', '要不', '要不然',
			'要不是', '要么', '要是', '也', '也罢', '也好', '一', '一般', '一旦',
			'一方面', '一来', '一切', '一样', '一则', '依', '依照', '矣', '以',
			'以便', '以及', '以免', '以至', '以至于', '以致', '抑或', '因',
			'因此', '因而', '因为', '哟', '用', '由', '由此可见', '由于', '又',
			'于', '于是', '于是乎', '与', '与此同时', '与否', '与其', '越是', '云云',
			'哉', '再说', '再者', '在', '在下', '咱', '咱们', '则', '怎', '怎么',
			'怎么办', '怎么样', '怎样', '咋', '照', '照着', '者', '这', '这边', '这儿',
			'这个', '这会儿', '这就是说', '这里', '这么', '这么点儿', '这么些',
			'这么样', '这时', '这些', '这样', '正如', '吱', '之', '之类', '之所以',
			'之一', '只是', '只限', '只要', '至', '至于', '诸位', '着', '着呢', '自',
			'自从', '自个儿', '自各儿', '自己', '自家', '自身', '综上所述', '总而言之',
			'总之', '纵', '纵令', '纵然', '纵使', '遵照', '作为', '兮', '呃', '呗', '咚',
			'咦', '喏', '啐', '喔唷', '嗬', '嗯', '嗳',
			'a\'s', 'able', 'about', 'above', 'according', 'accordingly', 'across', 'actually',
			'after', 'afterwards', 'again', 'against', 'ain\'t', 'all', 'allow', 'allows',
			'almost', 'alone', 'along', 'already', 'also', 'although', 'always', 'am',
			'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow',
			'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate',
			'appropriate', 'are', 'aren\'t', 'around', 'as', 'aside', 'ask', 'asking',
			'associated', 'at', 'available', 'away', 'awfully', 'be', 'became', 'because',
			'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'behind', 'being',
			'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond',
			'both', 'brief', 'but', 'by', 'c\'mon', 'c\'s', 'came', 'can',
			'can\'t', 'cannot', 'cant', 'cause', 'causes', 'certain', 'certainly', 'changes',
			'clearly', 'co', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider',
			'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course',
			'currently', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'do',
			'does', 'doesn\'t', 'doing', 'don\'t', 'done', 'down', 'downwards', 'during',
			'each', 'edu', 'eg', 'eight', 'either', 'else', 'elsewhere', 'enough',
			'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'every', 'everybody',
			'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'far',
			'few', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for',
			'former', 'formerly', 'forth', 'four', 'from', 'further', 'furthermore', 'get',
			'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone',
			'got', 'gotten', 'greetings', 'had', 'hadn\'t', 'happens', 'hardly', 'has',
			'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'s', 'hello', 'help',
			'hence', 'her', 'here', 'here\'s', 'hereafter', 'hereby', 'herein', 'hereupon',
			'hers', 'herself', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully',
			'how', 'howbeit', 'however', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'ie',
			'if', 'ignored', 'immediate', 'in', 'inasmuch', 'inc', 'indeed', 'indicate',
			'indicated', 'indicates', 'inner', 'insofar', 'instead', 'into', 'inward', 'is',
			'isn\'t', 'it', 'it\'d', 'it\'ll', 'it\'s', 'its', 'itself', 'just',
			'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'last', 'lately',
			'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s',
			'like', 'liked', 'likely', 'little', 'look', 'looking', 'looks', 'ltd',
			'mainly', 'many', 'may', 'maybe', 'me', 'mean', 'meanwhile', 'merely',
			'might', 'more', 'moreover', 'most', 'mostly', 'much', 'must', 'my',
			'myself', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need',
			'needs', 'neither', 'never', 'nevertheless', 'new', 'next', 'nine', 'no',
			'nobody', 'non', 'none', 'noone', 'nor', 'normally', 'not', 'nothing',
			'novel', 'now', 'nowhere', 'obviously', 'of', 'off', 'often', 'oh',
			'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'only',
			'onto', 'or', 'other', 'others', 'otherwise', 'ought', 'our', 'ours',
			'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'particular', 'particularly',
			'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably',
			'provides', 'que', 'quite', 'qv', 'rather', 'rd', 're', 'really',
			'reasonably', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'said',
			'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see',
			'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves',
			'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'she',
			'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'somehow',
			'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry',
			'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure',
			't\'s', 'take', 'taken', 'tell', 'tends', 'th', 'than', 'thank',
			'thanks', 'thanx', 'that', 'that\'s', 'thats', 'the', 'their', 'theirs',
			'them', 'themselves', 'then', 'thence', 'there', 'there\'s', 'thereafter', 'thereby',
			'therefore', 'therein', 'theres', 'thereupon', 'these', 'they', 'they\'d', 'they\'ll',
			'they\'re', 'they\'ve', 'think', 'third', 'this', 'thorough', 'thoroughly', 'those',
			'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to', 'together',
			'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try',
			'trying', 'twice', 'two', 'un', 'under', 'unfortunately', 'unless', 'unlikely',
			'until', 'unto', 'up', 'upon', 'us', 'use', 'used', 'useful',
			'uses', 'using', 'usually', 'value', 'various', 'very', 'via', 'viz',
			'vs', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d',
			'we\'ll', 'we\'re', 'we\'ve', 'welcome', 'well', 'went', 'were', 'weren\'t',
			'what', 'what\'s', 'whatever', 'when', 'whence', 'whenever', 'where', 'where\'s',
			'whereafter', 'whereas', 'whereby', 'wherein', 'whereupon', 'wherever', 'whether', 'which',
			'while', 'whither', 'who', 'who\'s', 'whoever', 'whole', 'whom', 'whose',
			'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'won\'t',
			'wonder', 'would', 'wouldn\'t', 'yes', 'yet', 'you', 'you\'d', 'you\'ll',
			'you\'re', 'you\'ve', 'your', 'yours', 'yourself', 'yourselves', 'zero'
		);

		if (in_array($keyword, $stop_words_list))
		{
			return false;
		}

		return true;
	}

	public function analysis_keyword($string)
	{
		$analysis = load_class('Services_Phpanalysis_Phpanalysis');

		$analysis->SetSource(strtolower($string));
		$analysis->StartAnalysis();

		if ($result = explode(',', $analysis->GetFinallyResult(',')))
		{
			$result = array_unique($result);

			foreach ($result as $key => $keyword)
			{
				if (!$this->check_stop_keyword($keyword))
				{
					unset($result[$key]);
				}
				else
				{
					$result[$key] = trim($keyword);
				}
			}
		}

		return $result;
	}

	public function clean_session()
	{
		return $this->delete('sessions', '`modified` < ' . (time() - 3600));
	}

	public function statistic($tag, $start_time = null, $end_time = null)
	{
		if (!$start_time)
		{
			$start_time = strtotime('-6 months');
		}

		if (!$end_time)
		{
			$end_time = strtotime('Today');
		}

		$data = array();

		switch ($tag)
		{
			case 'new_user':
				$query = "SELECT COUNT(uid) AS count, FROM_UNIXTIME(reg_time, '%y-%m') AS statistic_date FROM " . get_table('users') . " WHERE reg_time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_question':
				$query = "SELECT COUNT(question_id) AS count, FROM_UNIXTIME(add_time, '%y-%m') AS statistic_date FROM " . get_table('question') . " WHERE add_time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_answer':
				$query = "SELECT COUNT(answer_id) AS count, FROM_UNIXTIME(add_time, '%y-%m') AS statistic_date FROM " . get_table('answer') . " WHERE add_time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_topic':
				$query = "SELECT COUNT(topic_id) AS count, FROM_UNIXTIME(add_time, '%y-%m') AS statistic_date FROM " . get_table('topic') . " WHERE add_time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_answer_vote':
				$query = "SELECT COUNT(id) AS count, FROM_UNIXTIME(add_time, '%y-%m') AS statistic_date FROM " . get_table('vote') . " WHERE add_time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_favorite_item':
				$query = "SELECT COUNT(id) AS count, FROM_UNIXTIME(time, '%y-%m') AS statistic_date FROM " . get_table('favorite') . " WHERE time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;

			case 'new_question_redirect':
				$query = "SELECT COUNT(id) AS count, FROM_UNIXTIME(time, '%y-%m') AS statistic_date FROM " . get_table('redirect') . " WHERE time BETWEEN " . intval($start_time) . " AND " . intval($end_time) . " GROUP BY statistic_date ASC";
			break;
		}

		if ($query)
		{
			if ($result = $this->query_all($query))
			{
				foreach ($result AS $key => $val)
				{
					$data[] = array(
						'date' => $val['statistic_date'],
						'count' => $val['count']
					);
				}
			}
		}

		return $data;
	}

    public function update_topic_discuss_count($page, $limit = 100)
    {
        if (!$topics_list = $this->fetch_page('topic', null, 'topic_id ASC', $page, $limit))
        {
            return false;
        }

        foreach ($topics_list AS $key => $val)
        {
            $this->model('topic')->update_discuss_count($val['topic_id']);
        }

        return true;
    }
}