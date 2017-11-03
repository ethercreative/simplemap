<?php

namespace ether\simplemap;

class Variable {

	/**
	 * Returns the API key
	 *
	 * @return string
	 */
	public function getApiKey (): string
	{
		return SimpleMap::$plugin->getSettings()->apiKey;
	}

}