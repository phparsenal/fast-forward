<?php

namespace phparsenal\fastforward\Console;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Display a table with support for row wrapping.
 *
 * Example:
 *
 *     // Pass a Symfony\Component\Console\Output\OutputInterface
 *     $table = new Table($output);
 *     $table->setHeaders(array('Author', 'Title'));
 *     $table->addRow(array('Stanislaw Lem', 'The Futurological Congress));
 *     $table->render();
 *
 * @author Marcel Voigt <mv@noch.so>
 */
class Table
{
    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var array
     */
    private $rows = array();

    /**
     * @var OutputInterface
     */
    private $output;

    private $cellBorder = '|';
    private $outerBorder = '|';
    private $horizontalBorder = '-';
    private $padding = ' ';
    private $separatorCrossing = '+';

    /**
     * Max. amount of lines rendered in a cell.
     *
     * This only concerns rows with \n new lines in them. It will not limit the
     * amount of lines caused by wrapping.
     *
     * Set to zero for no limit.
     *
     * @var int
     */
    private $maxLines = 0;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the column headers.
     *
     * @param array $headers List of header names.
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_values($headers);
        return $this;
    }

    /**
     * Set an array of rows overwriting any existing ones.
     *
     * @param array $rows List of rows. Each row is a string array.
     *
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->rows = array();
        return $this->addRows($rows);
    }

    /**
     * Add an array of rows to the table.
     *
     * @param array $rows List of rows. Each row is a string array.
     *
     * @return $this
     */
    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     * Add a row to the table.
     *
     * @param array $row Column contents in a string array.
     *
     * @return $this
     */
    public function addRow(array $row)
    {
        if (!is_array($row)) {
            throw new \InvalidArgumentException('A row must be an array of strings.');
        }
        $this->rows[] = array_values($row);
        return $this;
    }

    /**
     * Renders the table via OutputInterface.
     */
    public function render()
    {
        $maxWidths = $this->getMaxContentWidths();
        $str = '';
        $str .= $this->formatSeparator($maxWidths);
        $str .= $this->formatRow($this->headers, $maxWidths);
        foreach ($this->rows as $row) {
            $str .= $this->formatSeparator($maxWidths);
            $str .= $this->formatRow($row, $maxWidths);
        }
        $str .= $this->formatSeparator($maxWidths);
        $this->output->writeln($str);
    }

    /**
     * Returns an array of maximum column widths.
     *
     * This will keep $maxLines in mind.
     */
    private function getMaxContentWidths()
    {
        // Initialize list with zero width
        $widths = array_fill(0, count($this->headers), 0);

        $rows = array_merge(array($this->headers), $this->rows);
        foreach ($rows as $row) {
            foreach ($row as $column => $columnContent) {
                foreach ($this->getLines($columnContent) as $line) {
                    $widths[$column] = max($widths[$column], strlen($line));
                }
            }
        }
        return $widths;
    }

    private function getLines($content)
    {
        $lines = explode(PHP_EOL, $content);
        $limit = count($lines);
        if ($this->maxLines !== 0) {
            $limit = min(count($lines), $this->maxLines);
        }
        return array_slice($lines, 0, $limit);
    }

    /**
     * @param array $maxWidths
     *
     * @return string
     */
    private function formatSeparator(array $maxWidths)
    {
        $str = '';
        $str .= $this->separatorCrossing;
        foreach ($maxWidths as $key => $width) {
            if ($key > 0) {
                $str .= $this->separatorCrossing;
            }
            $str .= str_pad('', strlen($this->padding), $this->horizontalBorder);
            $str .= str_pad('', $width, $this->horizontalBorder);
            $str .= str_pad('', strlen($this->padding), $this->horizontalBorder);
        }
        $str .= $this->separatorCrossing . PHP_EOL;
        return $str;
    }

    /**
     * @param array $row
     * @param array $maxWidths
     *
     * @return string
     */
    private function formatRow(array $row, array $maxWidths)
    {
        $columns = array();
        $maxLines = 0;
        foreach ($row as $column => $content) {
            $lines = $this->getLines($content);
            $maxLines = max($maxLines, count($lines));
            $columns[] = $lines;
        }
        $str = '';
        for ($i = 0; $i < $maxLines; $i++) {
            $str .= $this->outerBorder . $this->padding;
            foreach ($columns as $column => $content) {
                if ($column > 0) {
                    $str .= $this->padding;
                    $str .= $this->cellBorder;
                    $str .= $this->padding;
                }
                if ($i < count($content)) {
                    $str .= str_pad($content[$i], $maxWidths[$column], ' ', STR_PAD_RIGHT);
                } else {
                    $str .= str_pad('', $maxWidths[$column], ' ');
                }
            }
            $str .= $this->padding . $this->outerBorder . PHP_EOL;
        }
        return $str;
    }
}
