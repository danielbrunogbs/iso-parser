<?php

function error($message)
{
	$log = new \Illuminate\Support\Facades\Log();
	$log->info($message);
	return die();
}