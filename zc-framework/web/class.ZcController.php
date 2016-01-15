<?php

class ZcController {
	private $route;
	private $layout;

	public function __construct($route) {
		$this->route = $route;
	}

	/**
	 * 将HTTP请求转发给另一个控制器方法执行
	 *
	 * 如果是在Controller内部跳转，只要用方法名就可以了
	 *
	 * @param string $route
	 * @param array $args
	 * @return ZcAction
	 */
	protected function forward($route, $args = array()) {
		if (strpos($route, '/') === false) {
			//如果没有/，说明是在Controller内部跳转
			$route = substr($this->route, 0, strrpos($this->route, '/')) . '/' . $route;
		}
		return new ZcAction($route, $args);
	}


	protected function redirect($url, $status = 302) {
		header('Status: ' . $status);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
		exit();
	}

	/**
	 * 如果以后要采用Theme，那么就可以考虑重写本方法
	 */
	private function getViewFile($view) {
		//TODO 采用新的配置系统
		if (empty($view)) {
			$view = $this->route;
		}
		return Zc::C(ZcConfigConst::DirFsViewsPage) . $view . '.php';
	}

	/**
	 * 寻找Layout的方法
	 */
	private function getLayoutFiles($view) {
		if (empty($view)) {
			$view = $this->route;
		}
		$dirViewsLayout = Zc::C(ZcConfigConst::DirFsViewsLayout);

		$layoutFiles = array();
		$layoutFiles[] = $dirViewsLayout . 'default.php';

		$path = '';
		$parts = explode('/', $view);
		array_pop($parts);
		foreach ($parts as $part) {
			$path .= $part . '/';
			$layoutFiles[] = $dirViewsLayout . $path . 'default.php';
		}

		$layoutFiles[] = $dirViewsLayout . $view . '.php';

		$layoutFiles = array_reverse($layoutFiles);

		//想知道是按照什么顺序来寻找Layout的，把下面这行代码注释掉就行了
		//dump($layoutFiles);exit;
		return $layoutFiles;
	}

	protected function renderFile($file, $renderData, $return = false) {
		if(file_exists($file)) {
			extract($renderData, EXTR_OVERWRITE);

			ob_start();
			ob_implicit_flush(0);
			require($file);
			$content =  ob_get_clean();
				
			if ($return) {
				return $content;
			} else {
				echo $content;
			}

		} else {
			throw new Exception("Can not found $file in controller $this->route");
		}
	}

	/**
	 * 不渲染layout的render方法
	 *
	 * @param array $renderData
	 * @param string $view
	 * @param boolean $return
	 * @return string
	 */
	protected function renderWithoutLayout($renderData = array(), $view = null, $return = false) {
		return $this->render($renderData, $view, $return, false);
	}

	/**
	 * 渲染视图的方法
	 *
	 * @param  array $renderData 用于渲染的视图层的数据
	 * @param  string $view 视图层的模板，如果没有，就根据控制器当前的route来寻找
	 * @param  boolean $return 是否返回内容，而不是直接输出，如果需要再控制内部再做一些处理，比如静态化等，需要这个
	 * @param  boolean|string $layout 选择模板用什么layout文件，false表示不需要layout；null表示用当前的route；也可以指定一个layout
	 * @return string
	 */
	protected function render($renderData = array(), $view = null, $return = false, $layout = null) {
		$viewFile = $this->getViewFile($view);
		$output = $this->renderFile($viewFile, $renderData, true);

		//设置自定义layout
		if ($layout !== false) {
			$layout = is_null($layout)? $view : $layout;
			$layoutFiles = $this->getLayoutFiles($layout);
			foreach ($layoutFiles as $layoutFile) {
				if (file_exists($layoutFile)) {
					$output = $this->renderFile($layoutFile, array('_content_' => $output), true);
					break;
				}
			}
		}

		if($return) {
			return $output;
		} else {
			echo $output;
		}
	}
	
	/**
	 * 渲染输出json格式的内容
	 * @param array $data
	 */
	protected function renderJSON($data) {
		header('Content-type: text/json');
		echo json_encode($data);
	}
}