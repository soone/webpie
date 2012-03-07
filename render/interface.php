<?php
abstract class Webpie_Render_Interface
{
	abstract public function display(/*$tpl, $assign = NULL*/);
	abstract public function fetch(/*$tpl, $assign = NULL*/);
}
