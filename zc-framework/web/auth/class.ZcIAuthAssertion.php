<?php

interface ZcIAuthAssertion {

	/**
	 *
	 * @param ZcRbac $zcRbac
	 * @return bool
	 */
	public function assert(ZcRbac $zcRbac);
}