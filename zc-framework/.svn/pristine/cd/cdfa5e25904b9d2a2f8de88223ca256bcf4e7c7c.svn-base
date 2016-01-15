<?php
class ZcDispatcher {

	/**
	 * 执行控制器
	 * @param ZcAction $action
	 * @param ZcAction $error
	 */
	public function dispatch($action, $error  = null) {
		//处理过滤器，放在这个位置，说明只对类似Servlet规范的Request处理起作用
		$filters = ZcFactory::getConfig()->get(ZcConfigConst::Filters);
		if (!empty($filters)) {
			$matchFilterRoutes = array();
			foreach ($filters as $filter) {
				$matchNum = preg_match($filter['route.pattern'], $action->getRoute());
				if ($matchNum > 0) {
					$matchFilterRoutes[] = $filter['route'];
				}
			}
				
			foreach ($matchFilterRoutes as $matchFilterRoute) {
				$filterAction = new ZcAction($matchFilterRoute);
				$filterRetAction = $this->execute($filterAction);
				//当前filter可以执行三种操作：只是更改请求期间的数据，啥都不做；forward；redirect
				if ($filterRetAction && ($filterAction instanceof ZcAction)) {
					$action = $filterAction;
					break;
				}
			}
		}
			
		while ($action) {
			$action = $this->execute($action, $error);
		}
	}

	/**
	 *
	 * @param ZcAction $action
	 * @param ZcAction $error
	 */
	private function execute($action, $error) {
		if (file_exists($action->getFile())) {
				
			//加载语言
			ZcFactory::getLanguageObject()->loadControllerLanguageByRoute($action->getRoute());
				
			//实例化类
			require_once($action->getFile());
				
			$class = $action->getClass();
			$controller = new $class($action->getRoute());
				
			if (is_callable(array($controller, $action->getMethod()))) {
				
				if (is_callable(array($controller, 'beforeAction'))) {
					call_user_func_array(array($controller, 'beforeAction'), array());
				}
				
				$action = call_user_func_array(array($controller, $action->getMethod()), $action->getArgs());
				
				if (is_callable(array($controller, 'afterAction'))) {
					call_user_func_array(array($controller, 'afterAction'), array());
				}
			} else {
				$action = $error;
			}
		} else {
			$action = $error;
		}

		return $action;
	}
}
