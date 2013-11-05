<?php



class Social_Importer_LivestreetHelper_Html extends XenForo_Html_Renderer_BbCode
{
	protected $_handlers = array(
		'video' => array('wrap' => '[media=youtube]%s[/media]'),
		'b' => array('wrap' => '[B]%s[/B]'),
		'strong' => array('wrap' => '[B]%s[/B]'),

		'i' => array('wrap' => '[I]%s[/I]'),
		'em' => array('wrap' => '[I]%s[/I]'),

		'u' => array('wrap' => '[U]%s[/U]'),
		's' => array('wrap' => '[S]%s[/S]'),

		'a' => array('filterCallback' => array('$this', 'handleTagA')),
		'img' => array('filterCallback' => array('$this', 'handleTagImg')),

		'ul' => array('wrap' => "[LIST]\n%s\n[/LIST]", 'skipCss' => true),
		'ol' => array('wrap' => "[LIST=1]\n%s\n[/LIST]", 'skipCss' => true),
		'li' => array('filterCallback' => array('$this', 'handleTagLi')),

		'blockquote' => array('wrap' => '[INDENT]%s[/INDENT]'),

		'h1' => array('filterCallback' => array('$this', 'handleTagH')),
		'h2' => array('filterCallback' => array('$this', 'handleTagH')),
		'h3' => array('filterCallback' => array('$this', 'handleTagH')),
		'h4' => array('filterCallback' => array('$this', 'handleTagH')),
		'h5' => array('filterCallback' => array('$this', 'handleTagH')),
		'h6' => array('filterCallback' => array('$this', 'handleTagH')),
	);

	public static function renderFromHtml($html, array $options = array())
	{
		$html = htmlspecialchars_decode($html);
		$html = preg_replace('#<((object)|(a))(.*)youtube.com(.*)v[/|\=](.+?)[\&|\?|\"](.*)<\/((object)|(a))>#', '[media=youtube]$6[/media]', $html);

		$html = preg_replace('#<object(.*)src\=\"(.*?)\"(.*)<\/object>#', '[url]$2[/url]', $html);
		$html = preg_replace('#<iframe(.*)src\=\"(.*?)\"(.*)<\/iframe>#', '[url]$2[/url]', $html);

        // for do4a
        $html = str_ireplace('do4a.com/uploads', 'img.do4a.com/uploads' , $html);

		$parser = new XenForo_Html_Parser($html);
		$renderer = new self($options);
		$parsed = $parser->parse();

		$rendered = $renderer->render($parsed);
		//echo '<pre>' . htmlspecialchars($rendered) . '</pre>'; exit;
		//$rendered=preg_replace('#media=youtube\](.*)=#','media=youtube\]',$rendered);

		return $rendered;
	}

}