<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Export extends InteractiveCommand
{
    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Export commands to a file or stdout.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Output file. Use stdin when omitted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bookmarks = Bookmark::select()->all();
        $yaml = $this->getOutput($bookmarks);
        $file = $input->getArgument('file');
        if ($file !== null) {
            if (file_put_contents($file, $yaml) !== false) {
                $this->out->success('Exported ' . count($bookmarks) . ' bookmarks to ' . $file);
            } else {
                $this->out->error('Could not write to ' . $file);
            }
        } else {
            $this->out->write($yaml);
        }
    }

    /**
     * @param \nochso\ORM\ResultSet|Bookmark[] $bookmarks
     *
     * @return string
     */
    private function getOutput($bookmarks)
    {
        $map = array();
        foreach ($bookmarks as $bookmark) {
            $bookmark->id = null;
            $map[] = $bookmark->toAssoc();
        }
        $app = $this->getApplication();

        $yaml = sprintf("# %s %s\n", $app->getName(), $app->getVersion());
        $yaml .= sprintf("# Exported %u bookmarks.\n", count($map));
        $yaml .= sprintf("# %s\n", date(DATE_ISO8601));
        $yaml .= Yaml::dump($map);
        return $yaml;
    }
}
