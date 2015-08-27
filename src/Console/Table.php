<?php

namespace phparsenal\fastforward\Console;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Display a table with multi-line rows and auto-sized columns.
 *
 * Example:
 *
 *     // Pass a Symfony\Component\Console\Output\OutputInterface
 *     $table = new Table($output);
 *     $table->setTerminalWidth(120);
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
     * @var array
     */
    private $maxColumnWidths;

    /**
     * @var array
     */
    private $columnWidths;

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
     * @var int
     */
    private $terminalWidth = 0;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the maximum width of this table.
     *
     * When width is positive the rows will be resized and wrapped to fit.
     * Otherwise rows will be as wide as they need to be.
     *
     * @param int $width
     *
     * @return $this
     */
    public function setTerminalWidth($width)
    {
        $this->terminalWidth = $width;
        return $this;
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
        if ($this->terminalWidth === 0) {
            // No limit to line length
            $this->columnWidths = $this->getMaxColumnWidths();
        } else {
            $this->autoSizeContentWidths();
        }

        $str = '';
        $str .= $this->formatSeparator($this->columnWidths);
        $str .= $this->formatRow($this->headers, $this->columnWidths);
        foreach ($this->rows as $row) {
            $str .= $this->formatSeparator($this->columnWidths);
            $str .= $this->formatRow($row, $this->columnWidths);
        }
        $str .= $this->formatSeparator($this->columnWidths);
        $this->output->writeln($str);
    }

    /**
     * Get complete list of all widths of all columns.
     *
     * This will keep $maxLines in mind.
     *
     * @return array
     */
    private function getColumnWidths()
    {
        // Initialize empty lists for each column
        $widths = array_fill(0, count($this->headers), array());

        // Collect all line lengths per column
        $rows = array_merge(array($this->headers), $this->rows);
        foreach ($rows as $row) {
            foreach ($row as $column => $columnContent) {
                // Split cell content into lines
                foreach ($this->getLines($columnContent) as $line) {
                    $widths[$column][] = strlen($line);
                }
            }
        }
        return $widths;
    }

    /**
     * Returns an array of maximum column widths.
     *
     * @return array
     */
    private function getMaxColumnWidths()
    {
        $maxWidths = array();
        foreach ($this->getColumnWidths() as $column => $widthList) {
            $maxWidths[$column] = max($widthList);
        }
        return $maxWidths;
    }

    /**
     * Gets average and weighted column widths.
     *
     * @param int $spread Increasing this will favor smaller columns.
     *
     * @return array
     */
    private function getWeightedContentWidths($spread = 0)
    {
        // Calculate average widths and add spread
        $widths = array();
        foreach ($this->getColumnWidths() as $column => $widthList) {
            $widths[$column] = (int)(array_sum($widthList) / count($widthList)) + $spread;
        }
        return $widths;
    }

    private function autoSizeContentWidths()
    {
        // Format an empty row
        $emptyRow = array_fill(0, count($this->headers), '');
        $zeroWidth = array_fill(0, count($this->headers), 0);

        // to get the amount of characters reserved for styling (excluding line feed)
        $styleWidth = strlen($this->formatRow($emptyRow, $zeroWidth)) - 1;

        // How much can be used for actual content
        $availableWidth = $this->terminalWidth - $styleWidth;

        // Maximum content width per column without wrapping
        $this->maxColumnWidths = $this->getMaxColumnWidths();

        // Check if everything already fits.
        if (array_sum($this->maxColumnWidths) <= $availableWidth) {
            $this->columnWidths = $this->maxColumnWidths;
            return;
        }

        // Get weighted content widths favoring smaller columns
        $this->columnWidths = $this->getWeightedContentWidths(10);

        // Sum of weighted content widths
        $maxWidthSum = array_sum($this->columnWidths);

        // Factor to resize the columns
        $factor = $availableWidth / $maxWidthSum;

        // How many characters are left to spend
        $remaining = $availableWidth;
        $this->resizeColumns($factor, $remaining);
        $this->distributeRemaining($remaining);
    }

    /**
     * @param float $factor
     * @param int   $remaining
     *
     * @return array
     */
    private function resizeColumns($factor, &$remaining)
    {
        foreach ($this->columnWidths as $key => $width) {
            // Apply factor and round down to be safe
            $newWidth = (int)($this->columnWidths[$key] * $factor);

            // Use only as much as is needed
            $newWidth = min($newWidth, $this->maxColumnWidths[$key]);
            $this->columnWidths[$key] = $newWidth;
            $remaining -= $newWidth;
        }
    }

    /**
     * @param int $remaining
     */
    private function distributeRemaining(&$remaining)
    {
        // There are characters to spare. Distribute them on smaller columns first.
        $keys = $this->columnWidths;

        // Sort the current widths maintaining their keys
        asort($keys);

        // Get column indexes sorted by their size
        $keys = array_keys($keys);

        $changing = true;
        while ($changing && $remaining > 0) {
            $changing = false;
            for ($i = 0; $i < count($keys) && $remaining > 0; $i++) {
                $key = $keys[$i];
                // Only add when needed
                if ($this->columnWidths[$key] < $this->maxColumnWidths[$key]) {
                    $this->columnWidths[$key]++;
                    $remaining--;
                    $changing = true;
                }
            }
        }
    }

    private function getLines($content, $wrap = 0)
    {
        $lines = explode(PHP_EOL, $content);
        $limit = count($lines);
        if ($this->maxLines !== 0) {
            $limit = min(count($lines), $this->maxLines);
        }
        $lines = array_slice($lines, 0, $limit);
        if ($wrap > 0) {
            $wrappedLines = array();
            foreach ($lines as $line) {
                if (strlen($line) > $wrap) {
                    $wrappedLines = array_merge($wrappedLines, str_split($line, $wrap));
                } else {
                    $wrappedLines[] = $line;
                }
            }
            $lines = $wrappedLines;
        }
        return $lines;
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
            $lines = $this->getLines($content, $maxWidths[$column]);
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
