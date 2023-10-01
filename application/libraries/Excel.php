<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/** PHPExcel */
class Excel
{
   function Excel()
  {
      require_once APPPATH.'/libraries/excel/PHPExcel.php';
      require_once APPPATH.'/libraries/excel/PHPExcel/IOFactory.php';
  }
} 