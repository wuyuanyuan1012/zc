<?php

abstract class ZcWidget {

	/**
	 +----------------------------------------------------------
	 * 渲染输出 render方法是Widget唯一的接口
	 * 使用字符串返回 不能有任何输出
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param mixed $data  要渲染的数据
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	abstract public function render($renderData = '');

	/**
	 +----------------------------------------------------------
	 * 渲染模板输出 供render方法内部调用
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $templateFile  模板文件
	 * @param mixed $var  模板变量
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	*/
	protected function renderFile($templateFile = '', $renderData = '') {
		ob_start();
		ob_implicit_flush(0);

		if(!empty($renderData)) {
			extract($renderData, EXTR_OVERWRITE);
		}

		$widgetFile = Zc::C(ZcConfigConst::DirFsViewsWidget) . $templateFile . '.php';
		if( file_exists($widgetFile) ) {
			include ($widgetFile);
		}

		$content = ob_get_clean();
		return $content;
	}
}