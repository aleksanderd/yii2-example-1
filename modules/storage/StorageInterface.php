<?php

namespace jet\storage;

/**
 *
 */
interface StorageInterface
{
	public function save($file, $name, $options);

}