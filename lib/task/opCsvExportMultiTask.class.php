<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

if (!class_exists('opMultiExecutableTaskBaseTask'))
{
  class opCsvExportMultiTask extends sfBaseTask
  {
    protected function configure()
    {
      $this->namespace        = 'opCsvExport';
      $this->name             = 'export-multi';
    }
    protected function execute($arguments = array(), $options = array())
    {
      echo 'please install opMultiExecutableTaskPlugin'."\n";
    }
  }
  return;
}

/**
 * opCsvExportTaskMulti
 *
 * @package    opCsvExport
 * @author     Yuya Watanabe <watanabe@tejimaya.com>
 */
class opCsvExportMultiTask extends opMultiExecutableTaskBaseTask
{
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),

      new sfCommandOption('from', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 1),
      new sfCommandOption('to', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', null),

      new sfCommandOption('header', null, sfCommandOption::PARAMETER_OPTIONAL, 'need to output csv param header', true),

      new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 10),
    ));

    $this->namespace        = 'opCsvExport';
    $this->name             = 'export-multi';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->count = $options['number'];
    unset($options['number']);

    parent::execute($arguments, &$options);

    unset($arguments['task']);

    $task = new opCsvExportTask($this->dispatcher, $this->formatter);
    $task->run($arguments, $options);
  }

  protected function nextOptions()
  {
    new sfDatabaseManager($this->configuration);
    $max = Doctrine::getTable('Member')->createQuery()->select('max(id)')->execute(array(), Doctrine::HYDRATE_NONE);

    $i = 0;
    $count = $this->count;
    $header = true;

    return function() use (&$i, $count, $max, &$header)
    {
      if ($i > $max)
      {
        return false;
      }

      $result = array(
        'from' => $i,
        'to' => $i + $count,
        'header' => $header,
      );

      $i += $count;
      $header = false;

      return $result;
    };
  }
}
