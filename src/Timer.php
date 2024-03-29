<?php
/*******************************************************************************
 * @name: Timer
 * @note: Stopwatch in milliseconds script time+ the various steps in the code
 * @author: Jgauthi, created at [28mars2007], url: <https://github.com/jgauthi/component_debug>
 * @version: 2.1

 *******************************************************************************/

namespace Jgauthi\Component\Debug;

class Timer
{
    public const EXPORT_FORMAT_HTML = 'html';
    public const EXPORT_FORMAT_COMMENT = 'commentaire';

    protected float $startTime;
    protected array $time = [];
    protected array $chapterTimes = [];
    protected string $result;
    protected string $step;
    protected string $chapter;

    public function __construct(private bool $sendHeaderHttp = false)
    {
        $this->startTime = microtime(true);
    }

    static public function init(bool $header = false, string $format = self::EXPORT_FORMAT_HTML): self
    {
        $timer = new self($header);
        $timer->shutdown($format);

        return $timer;
    }

    //-- ETALONNER LE PARCOURS DU SCRIPT --------------
    public function step(string $nom): self
    {
        $this->time[] = ['nom' => $nom, 'time' => microtime(true)];

        return $this;
    }

    public function chapterStart(string $nom): self
    {
        $this->chapterTimes[$nom] = ['start' => microtime(true)];

        return $this;
    }

    public function chapterEnd(string $nom): self
    {
        $this->chapterTimes[$nom]['end'] = microtime(true);

        return $this;
    }

    //-- METTRE FIN AU COMPTEUR

    public function stop(): self
    {
        $endTime = microtime(true);

        // Vérifie si le header est correcte
        if ($this->sendHeaderHttp && headers_sent()) {
            $this->sendHeaderHttp = false;
        }

        // Analyse des données
        $this->result = $this->_conv($endTime - $this->startTime);

        // Analyse des étapes
        if (count($this->time) > 0) {
            $this->step = '';
            foreach ($this->time as $id => $tmp) {
                $calcul = $this->number_format($this->_conv($tmp['time'] - $this->startTime));
                $this->step .= "- {$tmp['nom']}: $calcul ms".PHP_EOL;

                if ($this->sendHeaderHttp) {
                    header("$id-Time: $calcul ms->{$tmp['nom']}");
                }
            }
        }

        // Analyse des chapitres
        if (count($this->chapterTimes) > 0) {
            $this->chapter = '';
            foreach ($this->chapterTimes as $id => $temp) {
                $calcul = $this->number_format($this->_conv($temp['end'] - $temp['start']));
                $this->chapter .= "- {$id}: $calcul ms".PHP_EOL;

                if ($this->sendHeaderHttp) {
                    header("{$id}-Chap: {$calcul} ms");
                }
            }
        }

        // Renvoi des données
        if ($this->sendHeaderHttp) {
            header('X-Time: '.$this->number_format($this->result).' ms');
        }

        return $this;
    }

    public function outPut(?string $type = null): string
    {
        $output = '';

        // Organiser les données
        if (count($this->time) > 0) {
            $output .= 'Step:'.PHP_EOL.$this->step;
        }
        if (count($this->chapterTimes) > 0) {
            $output .= 'Chapter:'.PHP_EOL.$this->chapter;
        }
        $output .= PHP_EOL.'=>Time: '.$this->number_format($this->result).' ms';

        // Formater les données
        if ($type === self::EXPORT_FORMAT_HTML) {
            $output = '<h2>Script time:</h2>'.PHP_EOL.
                str_replace(["\r\n", "\r", "\n"], '<br>', $output);
        } elseif ($type === self::EXPORT_FORMAT_COMMENT) {
            $output = '<!- TIME OF SCRIPT: '.PHP_EOL.$output.PHP_EOL.'-->';
        }

        return $output;
    }

    public function end(string $format = self::EXPORT_FORMAT_HTML): void
    {
        $this->stop();
        echo $this->outPut($format);
    }

    public function shutdown(string $format = self::EXPORT_FORMAT_HTML): void
    {
        register_shutdown_function([$this, 'end'], $format);
    }

    //-- CONVERTISSEUR ---------------------------------

    protected function _conv(float $time): string
    {
        return $time * 1000;
//        return mb_substr($time * 1000, 0, 6);

        // Récupère les 6 premiers chiffres
        // Convertie en millisecondes
        // Remplace . par , (écriture FR)
    }

    protected function number_format(float $chiffre): string
    {
        return number_format($chiffre, 2, ',', ' ');
    }

    //-- Outil de test/debug -------------------------------

    public function testLoop(callable $function, int $nb_loop = 10, int $nb = 100, array $args = []): self
    {
        $chapitre = preg_replace('#[^a-z0-9-_]#i', '', mb_substr($function, 0, 50));

        for ($loop = 0; $loop < $nb_loop; ++$loop) {
            $this->chapterStart($chapitre.'#'.$loop);
            for ($i = 0; $i < $nb; ++$i) {
                call_user_func_array($function, $args);
            }

            $this->chapterEnd($chapitre.'#'.$loop);
        }

        return $this;
    }

    /**
     * Method stand-alone
     * Usage: \Jgauthi\Component\Timer::loopFunction(function() use ($var): void { somecode($var); } );
     */
    public static function loopFunction(callable $closure, int $nbLoop = 100): float
    {
        $data = [];
        for($i = 0; $i < $nbLoop; $i++) {
            $time = microtime(true);
            $closure();
            $data[] = microtime(true) - $time;
        }

        return array_sum($data) / $nbLoop;
    }

    /**
     * Exporte un chapitre au format CSV.
     */
    public function exportChapter(string $filename, string $delimiter = ';'): void
    {
        // Analyse des chapitres
        if (empty($this->chapterTimes)) {
            return;
        }

        foreach ($this->chapterTimes as $id => $temp) {
            $calcul = $this->number_format($this->_conv($temp['end'] - $temp['start']));
            error_log("{$id}{$delimiter}{$calcul};ms".PHP_EOL, 3, $filename);
        }
    }
}

