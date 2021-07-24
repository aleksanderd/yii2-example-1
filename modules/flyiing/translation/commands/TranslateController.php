<?php

namespace flyiing\translation\commands;

use flyiing\translation\models\TMessage;
use flyiing\translation\models\TSourceMessage;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

class TranslateController extends Controller
{

    public function actionImport($messagesPath, $overwrite = false)
    {
        if (!$messagesPath) {
            $messagesPath = $this->prompt('Enter a source path');
        }
        $messagesPath = realpath(Yii::getAlias($messagesPath));
        if (!is_dir($messagesPath)) {
            throw new Exception('The source path ' . $messagesPath . ' is not a valid directory.');
        }

        $files = FileHelper::findFiles($messagesPath, ['only' => ['*.php']]);
        foreach ($files as $file) {
            $this->stdout($file . ': ');
            $relPath = str_replace([$messagesPath, '.php'], '', $file);
            $relPath = trim($relPath, '/,\\');
            $relPath = FileHelper::normalizePath($relPath, '/');
            $parts = explode('/', $relPath, 2);
            if (count($parts) > 1) {
                $language = $parts[0];
                $category = $parts[1];
                if (is_array($t = require_once($file))) {
                    $this->stdout(sprintf('Language = %s, Category = %s, importing...' . PHP_EOL, $language, $category));
                    foreach ($t as $sourceMessage => $translation) {
                        $sm = TSourceMessage::findOrCreate($category, $sourceMessage);
                        $m = TMessage::findOrCreate($sm, $language, $translation, $overwrite);
                        $this->stdout(sprintf('%d:%s:%s => %s:%s' . PHP_EOL,
                            $sm->id, $sm->category, $sm->message, $m->language, $m->translation));
                    }
                }

            } else {
                echo 'Skipped.' . PHP_EOL;
            }
        }

    }

    protected function exportPhpValue($value)
    {
        if (!(isset($value) && is_string($value))) {
            $value = '';
        }
        $count = 0;
        $value = str_replace(["\r\n", "\n", "\r"], ['\r\n', '\n', '\r'], $value, $count);
        $s = $count > 0 ? '"' : "'";
        return $s . str_replace($s, '\\' . $s, $value) . $s;
    }

    protected function exportPhp($filename, $translation) {
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL . 'return [' . PHP_EOL;
        foreach ($translation as $k => $v) {
            $ak = $this->exportPhpValue($k);
            $av = $this->exportPhpValue($v);
            if ($ak != "''") {
                $content .= '  ' . $ak . ' => ' . $av . ',' . PHP_EOL;
            }
        }
        $content .= '];' . PHP_EOL;
        return file_put_contents($filename, $content);
    }

    public function actionExport($messagesPath, $overwrite = false)
    {
        if (!$messagesPath) {
            $messagesPath = $this->prompt('Enter a source path');
        }
        $messagesPath = Yii::getAlias($messagesPath);
        FileHelper::createDirectory($messagesPath);
        $messagesPath = realpath($messagesPath);

        $t = [];
        /** @var TMessage[] $messages */
        $messages = TMessage::find()->all();
        $total = count($messages);
        $done = 0;
        $oPct = '';
        foreach ($messages as $message) {
            $s = $message->source->message;
            $l = $message->language;
            $c = $message->source->category;
            if (!isset($t[$l][$c])) {
                if (is_readable($filename = $messagesPath . '/' . $l . '/' . $c . '.php')) {
                    $t[$l][$c] = require_once($filename);
                } else {
                    $t[$l][$c] = [];
                }
            }
            if (!isset($t[$l][$c][$s]) || strlen($t[$l][$c][$s]) < 1 || $overwrite) {
                $t[$l][$c][$s] = $message->translation;
            }
            if (($pct = sprintf('%d%%', 100 * $done++ / $total)) != $oPct) {
                $this->stdout("\r" . 'Collecting messages... ');
                $this->stdout($pct);
                $oPct = $pct;
            }
        }
        $this->stdout("\r" . 'Collecting messages... ');
        $this->stdout('done!' . PHP_EOL, Console::FG_GREEN);

        $this->stdout('Writing the files...' . PHP_EOL);
        foreach ($t as $l => $cats) {
            $this->stdout('  Language: ' . $l . PHP_EOL);
            foreach ($cats as $c => $tt) {
                $this->stdout('    Category: ' . $c . PHP_EOL);
                FileHelper::createDirectory($messagesPath . '/' . $l);
                $filename = $messagesPath . '/' . $l . '/' . $c . '.php';
                $this->stdout('    Filename: ' . $filename . PHP_EOL);
                $this->stdout('    Writing... ');
                $res = $this->exportPhp($filename, $tt);
                if ($res === false) {
                    $this->stdout('failed :(', Console::FG_RED);
                } else {
                    $this->stdout('done!', Console::FG_GREEN);
                    $this->stdout(sprintf(' %d bytes written.', $res), Console::FG_GREY);
                }
                $this->stdout(PHP_EOL);
            }
        }

    }

}