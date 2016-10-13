<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 27.09.16
 * Time: 14:41
 */

namespace MediaLicense;


class CreativeCommon {
	private $slug;
	private $license;
	/**
	 * CreativeCommon constructor.
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function __construct($slug) {
		$list = self::getList();
		if(!isset($list[$slug]) || empty($list[$slug])){
			$this->license = null;
			return;
		}
		$this->license = $list[$slug];
	}
	
	/**
	 * get list of all licenses
	 * @return array
	 */
	public static function getList(){
		$licenses = array();
		
		$licenses['nil'] = array(
			'label'     => __('-- Choose license --', 'media_license'),
			'path'      => '',
		);
		
		$licenses['copyright'] = array(
			'label'     => __('All rights reserved', 'media_license'),
			'path'      => '',
		);
		
		/**
		 * CC-BY
		 */
		$licenses['cc-by'] = array(
			'label'     => 'CC-BY 2.0',
			'path'      => 'by/2.0',
		);
		
		$licenses['cc-by-3'] = array(
			'label'     => 'CC-BY 3.0',
			'path'      => 'by/3.0',
		);
		
		$licenses['cc-by-4'] = array(
			'label'     => 'CC-BY 4.0',
			'path'      => 'by/4.0',
		);
		
		/**
		 * CC-BY-SA
		 */
		$licenses['cc-by-sa'] = array(
			'label'     => 'CC-BY-SA 2.0',
			'path'      => 'by-sa/2.0',
		);
		
		$licenses['cc-by-sa-3'] = array(
			'label'     => 'CC-BY-SA 3.0',
			'path'      => 'by-sa/3.0',
		);
		
		$licenses['cc-by-sa-4'] = array(
			'label'     => 'CC-BY-SA 4.0',
			'path'      => 'by-sa/4.0',
		);
		
		/**
		 * CC-BY-NC
		 */
		$licenses['cc-by-nc'] = array(
			'label'     => 'CC-BY-NC 2.0',
			'path'      => 'by-nc/2.0',
		);
		
		$licenses['cc-by-nc-3'] = array(
			'label'     => 'CC-BY-NC 3.0',
			'path'      => 'by-nc/3.0',
		);
		
		$licenses['cc-by-nc-4'] = array(
			'label'     => 'CC-BY-NC 4.0',
			'path'      => 'by-nc/4.0',
		);
		
		/**
		 * CC-BY-ND
		 */
		$licenses['cc-by-nd'] = array(
			'label'     => 'CC-BY-ND 2.0',
			'path'      => 'by-nd/2.0',
		);
		
		$licenses['cc-by-nd-3'] = array(
			'label'     => 'CC-BY-ND 3.0',
			'path'      => 'by-nd/3.0',
		);
		
		$licenses['cc-by-nd-4'] = array(
			'label'     => 'CC-BY-ND 4.0',
			'path'      => 'by-nd/4.0',
		);
		
		/**
		 * CC-BY-NC-SA
		 */
		$licenses['cc-by-nc-sa'] = array(
			'label'     => 'CC-BY-NC-SA 2.0',
			'path'      => 'by-nc-sa/2.0',
		);
		
		$licenses['cc-by-nc-sa-3'] = array(
			'label'     => 'CC-BY-NC-SA 3.0',
			'path'      => 'by-nc-sa/3.0',
		);
		
		$licenses['cc-by-nc-sa-4'] = array(
			'label'     => 'CC-BY-NC-SA 4.0',
			'path'      => 'by-nc-sa/4.0',
		);
		
		/**
		 * CC-BY-NC-ND
		 */
		$licenses['cc-by-nc-nd'] = array(
			'label'     => 'CC-BY-NC-SA 2.0',
			'path'      => 'by-nc-nd/2.0',
		);
		
		$licenses['cc-by-nc-nd-3'] = array(
			'label'     => 'CC-BY-NC-SA 3.0',
			'path'      => 'by-nc-nd/3.0',
		);
		
		$licenses['cc-by-nc-nd-4'] = array(
			'label'     => 'CC-BY-NC-SA 4.0',
			'path'      => 'by-nc-nd/4.0',
		);
		
		/**
		 * public domain
		 */
		$licenses['public-domain'] = array(
			'label'     => __('Public Domain', 'media_license'),
			'path'      => '',
		);
		
		return $licenses;
	}
	
	/**
	 * check if license is chosen
	 * @return bool
	 */
	public function hasLicense(){
		return ($this->license != null && $this->license['path'] != '');
	}
	
	/**
	 * @param $slug
	 * @param $content
	 * @param array $classes
	 *
	 * @return string
	 */
	public function getLink($content, $classes = array()){
		if(!$this->hasLicense()) return $content;
		return '<a class="'.implode(" ", $classes).'" rel="license" target="_blank" href="'.$this->getUrl() . '">' . $content . '</a>';
	}
	
	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	public function getImage( $classes = array()){
		if(!$this->hasLicense()) return '';
		return '<img class="'.implode(" ", $classes).'" alt="'.__("Creative Commons License logo").'" src="'.$this->getImageUrl().'" />';
	}
	
	/**
	 * @param $slug
	 *
	 * @return string url to license
	 */
	public function getUrl(){
		if(!$this->hasLicense()) return '';
		return "http://creativecommons.org/licenses/".$this->license['path']."/deed.de";
	}
	
	/**
	 * @param $slug
	 *
	 * @return mixed
	 */
	public function getImageUrl(){
		if(!$this->hasLicense()) return '';
		return "https://i.creativecommons.org/l/".$this->license['path']."/80x15.png";
	}
	
	/**
	 * get license label
	 *
	 * @return string
	 */
	public function getLabel( ) {
		if(!$this->hasLicense()) return '';
		return $this->license['label'];
	}
	
}