<?php

return [
	'description' => 'Fail',
	'command' => function () {
		throw new Exception('Something went wrong');
	}
];
