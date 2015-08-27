<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Import extends InteractiveCommand
{
    private $failed;
    private $data;
    private $imported;
    private $skipped;

    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Import bookmarks from a file or pipe.')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to import. Use stdin when omitted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if ($file !== null) {
            $this->data = $this->getFileContent($file);
            if ($this->data === null) {
                return;
            }
        } else {
            $this->data = $this->getStdin();
        }

        $mapList = Yaml::parse($this->data);
        if (!is_array($mapList)) {
            $this->out->error('Unable to parse data for import.');
            return;
        }

        $this->importMapList($mapList);
        $this->showResults();
    }

    private function getFileContent($file)
    {
        if (!is_file($file)) {
            $this->out->error('Unable to find file ' . $file);
            return null;
        }

        $data = file_get_contents($file);
        if ($data === false) {
            $this->out->error('Unable to read file ' . $file);
            return null;
        }

        return $data;
    }

    /**
     * @param array $bookmarkMap List of bookmarks from parsed YAML file
     */
    private function importMapList($bookmarkMap)
    {
        $this->imported = array();
        $this->skipped = array();
        foreach ($bookmarkMap as $map) {
            if (is_array($map)) {
                $this->importMap($map);
            }
        }
    }

    /**
     * @param array $map
     */
    private function importMap($map)
    {
        $bookmark = new Bookmark();
        $bookmark->hydrate($map);
        $existing = Bookmark::select()->where('shortcut', $bookmark->shortcut)->one();
        if ($existing !== null) {
            $bookmark->setExtra('Existing command', $existing->command);
            $this->skipped[] = $bookmark;
            return;
        }

        try {
            $bookmark->save();
            $this->imported[] = $bookmark;
        } catch (\Exception $e) {
            $bookmark->setExtra('Error', $e->getMessage());
            $this->failed[] = $bookmark;
        }
    }

    private function showResults()
    {
        if (count($this->failed) > 0) {
            $this->out->note('Bookmarks that failed to import: ' . count($this->failed));
            Bookmark::table($this->out, $this->failed, array('Error'));
        }
        if (count($this->skipped) > 0) {
            $this->out->note('Bookmarks skipped because of identical shortcuts: ' . count($this->skipped));
            Bookmark::table($this->out, $this->skipped, array('Existing command'));
        }
        if (count($this->imported) > 0) {
            $this->out->success('Bookmarks successfully imported: ' . count($this->imported));
            Bookmark::table($this->out, $this->imported);
        } else {
            $this->out->note('No bookmarks were imported.');
        }
    }
}
