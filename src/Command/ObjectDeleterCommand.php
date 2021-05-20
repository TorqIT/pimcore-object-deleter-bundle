<?php

namespace TorqIT\ObjectDeleterBundle\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ObjectDeleterCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('torq:object-deleter')
            ->setDescription('Delete objects in bulk by class name and root directory')
            ->addArgument('classname', InputArgument::REQUIRED, 'Name of class to run on')
            ->addArgument('root', InputArgument::OPTIONAL, "Root folder to delete from", '/');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('classname');
        $root = $input->getArgument('root');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This command cannot be undone. Are you sure you want to continue? [Yes | No]: ', false, '/^Yes|Y/i');

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Command cancelled");
            return;
        }

        $output->writeln("Continuing delete command");

        $deletedIds = $this->callDeleteProcedure($className, $root, $output);

        $this->deleteVersionFolders($deletedIds, $output);

        return 0;
    }

    private function callDeleteProcedure($className, $root, OutputInterface $output){
        $class = ClassDefinition::getByName($className);

        if(!$class){
            $output->writeln("Class $className does not exist!");
        }
        
        $classId = $class->getId();

        $db = \Pimcore\Db::get();

        $statement = $db->prepare("call delete_objects(:classId, :root)");
        $statement->bindParam(':classId', $classId, \PDO::PARAM_INPUT_OUTPUT, 32);
        $statement->bindParam(':root', $root, \PDO::PARAM_INPUT_OUTPUT, 32);
        $statement->execute();

        $result =$statement->fetchAll();
        
        $output->writeln("Deleting " .  count($result) . " $className records under path $root !");

        $cacheClearInput = new ArrayInput(array());
        $cacheCommand = $this->getApplication()->find('cache:clear');
        $cacheCommand->run($cacheClearInput, $output);

        return $result;
    }

    private function deleteVersionFolders($deletedIds, OutputInterface $output){
        $output->writeln("Deleting verions");

        foreach($deletedIds as $deletedIdArr){
            $deletedId = $deletedIdArr["o_id"];
            $deletedIdGroup = 10000 * floor( $deletedId / 10000);

            $vFolder = PIMCORE_PRIVATE_VAR . "/versions/object/g$deletedIdGroup/$deletedId";

            shell_exec("rm -rf $vFolder");
        }
    }
}