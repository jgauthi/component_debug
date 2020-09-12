<?php
/*******************************************************************************
 * @name: Timer
 * @note: Stopwatch in milliseconds script time+ the various steps in the code
 * @author: Jgauthi <github.com/jgauthi>, created at [2mars2007]
 * @version: 1.2

 *******************************************************************************/

namespace Jgauthi\Component\Debug;

class Timer
{
    const EXPORT_FORMAT_HTML = 'html';
    const EXPORT_FORMAT_COMMENT = 'commentaire';

    /** @var float */
    protected $startTime;
    /** @var array */
    protected $time = [];
    /** @var array */
    protected $chapterTimes = [];
    /** @var bool */
    private $sendHeaderHttp;
    /** @var string */
    protected $result;
    /** @var string */
    protected $step;
    /** @var string */
    protected $chapter;

    /**
     * @param bool $header
     */
    public function __construct($header = false)
    {
        $this->startTime = microtime(true);
        $this->sendHeaderHttp = $header;
    }

    /**
     * @param bool $header
     * @param string $format
     * @return self
     */
    static public function init($header = false, $format = self::EXPORT_FORMAT_HTML)
    {
        $timer = new self($header);
        $timer->shutdown($format);

        return $timer;
    }

    //-- ÉTALONNER LE PARCOURS DU SCRIPT --------------

    /**
     * @param string $nom
     * @return self
     */
    public function step($nom)
    {
        $this->time[] = ['nom' => $nom, 'time' => microtime(true)];

        return $this;
    }

    /**
     * @param string $nom
     * @return self
     */
    public function chapterStart($nom)
    {
        $this->chapterTimes[$nom] = ['start' => microtime(true)];

        return $this;
    }

    /**
     * @param string $nom
     * @return self
     */
    public function chapterEnd($nom)
    {
        $this->chapterTimes[$nom]['end'] = microtime(true);

        return $this;
    }

    //-- METTRE FIN AU COMPTEUR

    /**
     * @return self
     */
    public function stop()
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
                $this->step .= "- {$tmp['nom']}: $calcul ms\n";

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
                $this->chapter .= "- {$id}: $calcul ms\n";

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

    /**
     * @param string|null $type
     * @return string
     */
    public function outPut($type = null)
    {
        $output = '';

        // Organiser les données
        if (count($this->time) > 0) {
            $output .= "Step:\n".$this->step;
        }
        if (count($this->chapterTimes) > 0) {
            $output .= "Chapter:\n".$this->chapter;
        }
        $output .= "\n=>Time: ".$this->number_format($this->result).' ms';

        // Formater les données
        if ($type === self::EXPORT_FORMAT_HTML) {
            $output = "<h2>Script time:</h2>\n".
                str_replace(["\r\n", "\r", "\n"], '<br />', $output);
        } elseif ($type === self::EXPORT_FORMAT_COMMENT) {
            $output = "<!- TIME OF SCRIPT: \n$output\n-->";
        }

        return $output;
    }

    /**
     * @param string $format
     */
    public function end($format = self::EXPORT_FORMAT_HTML)
    {
        $this->stop();
        echo $this->outPut($format);
    }

    /**
     * @param string $format
     */
    public function shutdown($format = self::EXPORT_FORMAT_HTML)
    {
        register_shutdown_function([$this, 'end'], $format);
    }

    //-- CONVERTISSEUR ---------------------------------

    /**
     * @param float $time
     * @return string
     */
    protected function _conv($time)
    {
        return $time * 1000;
//        return mb_substr($time * 1000, 0, 6);

        // Récupère les 6 premiers chiffres
        // Convertie en millisecondes
        // Remplace . par , (écriture FR)
    }

    /**
     * @param float $chiffre
     * @return string
     */
    protected function number_format($chiffre)
    {
        return number_format($chiffre, 2, ',', ' ');
    }

    //-- Outil de test/debug -------------------------------

    /**
     * @param callable $function
     * @param int $nb_loop
     * @param int $nb
     * @param array $args
     * @return self
     */
    public function testLoop($function, $nb_loop = 10, $nb = 100, $args = [])
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
     * Exporte un chapitre au format CSV.
     * @param string $filename
     * @param string $delimiter
     */
    public function exportChapter($filename, $delimiter = ';')
    {
        // Analyse des chapitres
        if (empty($this->chapterTimes)) {
            return;
        }

        foreach ($this->chapterTimes as $id => $temp) {
            $calcul = $this->number_format($this->_conv($temp['end'] - $temp['start']));
            error_log("{$id}{$delimiter}{$calcul};ms\n", 3, $filename);
        }
    }
}

