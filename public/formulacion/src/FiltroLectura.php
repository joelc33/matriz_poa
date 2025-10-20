<?php namespace Reingsys;

class FiltroLectura implements \PHPExcel_Reader_IReadFilter {
	private $start;
	private $end;
	private $cols;

	public function __construct( $start, $end, $cols ) {
		$this->start = $start;
		$this->end = $end;
		$this->cols = $cols;
	}

	public function readCell( $column, $row, $ws = '' ) {
		//A = 1
		$colIdx = \PHPExcel_Cell::columnIndexFromString( $column ) - 1;
		if ( $row >= $this->start && $row <= $this->end ) {
			if ( array_search( $colIdx, $this->cols ) !== false ) {
				return true;
			}
		}
		return false;
	}
}

