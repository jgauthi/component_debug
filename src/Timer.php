<?php
/*******************************************************************************
 * @name: Timer
 * @note: Stopwatch in milliseconds script time+ the various steps in the code
 * @author: Jgauthi <github.com/jgauthi>, created at [2mars2007]
 * @version: 1.1

 *******************************************************************************/

namespace Jgauthi\Component\Debug;

class Timer
{
    protected $debut;
    protected $fin;
    protected $time = [];
    protected $time_chap = [];
    public $start = false;
    public $header = 0;
    public $resultat;
    protected $etape;
    protected $chapitre;

    //-- CONSTRUCTEUR ---------------------------------
    public function __construct($header = 0)
    {
        $this->debut = microtime();
        $this->header = $header;
    }

    //-- ETALONNER LE PARCOURS DU SCRIPT --------------

    /**
     * @param $nom
     * @return self
     */
    public function etape($nom)
    {
        $this->time[] = ['nom' => $nom, 'time' => microtime()];

        return $this;
    }

    /**
     * @param $nom
     * @return self
     */
    public function chapitre_debut($nom)
    {
        $this->time_chap[$nom] = ['debut' => microtime()];

        return $this;
    }

    /**
     * @param $nom
     */
    public function chapitre_fin($nom)
    {
        $this->time_chap[$nom]['fin'] = microtime();
    }

    //-- METTRE FIN AU COMPTEUR

    public function stop()
    {
        $this->fin = microtime();

        // Conversion
        $this->debut = $this->_unit($this->debut);
        $this->fin = $this->_unit($this->fin);

        // Vérifie si le header est correcte
        if ($this->header && headers_sent()) {
            $this->header = false;
        }

        // Analyse des données
        $this->resultat = $this->_conv($this->fin - $this->debut);

        // Analyse des étapes
        if (count($this->time) > 0) {
            $this->etape = '';
            foreach ($this->time as $id => $tmp) {
                $calcul = $this->_vir($this->_conv($this->_unit($tmp['time']) - $this->debut));
                $this->etape .= "- {$tmp['nom']}: $calcul ms\n";

                if ($this->header) {
                    header("$id-Time: $calcul ms->{$tmp['nom']}");
                }
            }
        }

        // Analyse des chapitres
        if (count($this->time_chap) > 0) {
            $this->chapitre = '';
            foreach ($this->time_chap as $id => $temp) {
                $calcul = $this->_vir($this->_conv($this->_unit($temp['fin']) - $this->_unit($temp['debut'])));
                $this->chapitre .= "- {$id}: $calcul ms\n";

                if ($this->header) {
                    header("$id-Chap: $calcul ms");
                }
            }
        }

        // Renvoi des données
        if ($this->header) {
            header('X-Time: '.$this->_vir($this->resultat).' ms');
        }
    }

    /**
     * @param null $type
     * @return string
     */
    public function OutPut($type = null)
    {
        $output = '';

        // Organiser les données
        if (count($this->time) > 0) {
            $output .= "Etape:\n".$this->etape;
        }
        if (count($this->time_chap) > 0) {
            $output .= "Chapitre:\n".$this->chapitre;
        }
        $output .= "\n=>Time: ".$this->_vir($this->resultat).' ms';

        // Formater les données
        if ('html' === $type) {
            $outpub = "<h2>Temps du script:</h2>\n".
                str_replace(["\r\n", "\r", "\n"], '<br />', $output);
        } elseif ('commentaire' === $type) {
            $outpub = "<!- TEMPS ECOULE DU SCRIPT: \n$output\n-->";
        }

        return $outpub;
    }

    /**
     * @return string
     */
    public function temps()
    {
        return $this->_vir($this->resultat);
    }

    /**
     * @param string $format
     */
    public function end($format = 'html')
    {
        $this->stop();
        echo $this->OutPut($format);
    }

    /**
     * @param string $format
     */
    public function shutdown($format = 'html')
    {
        register_shutdown_function([$this, 'end'], 'html');
    }

    //-- CONVERTISSEUR ---------------------------------

    /**
     * @param $time
     * @return mixed
     */
    protected function _unit($time)
    {
        list($usec, $sec) = explode(' ', $time);

        return $usec + $sec;
    }

    /**
     * @param $time
     * @return string
     */
    protected function _conv($time)
    {
        return mb_substr($time * 1000, 0, 6);

        // Récupère les 6 premiers chiffres
        // Convertie en millisecondes
        // Remplace . par , (écriture FR)
    }

    /**
     * @param $chiffre
     * @return string
     */
    protected function _vir($chiffre)
    {
        return number_format($chiffre, 2, ',', ' ');
    }

    //-- Outil de test/debug -------------------------------

    /**
     * @param string $phpcode
     * @param int $nb_loop
     * @param int $nb
     */
    public function testloop($phpcode, $nb_loop = 10, $nb = 100)
    {
        $chapitre = preg_replace('#[^a-z0-9-_]#i', '', mb_substr($phpcode, 0, 50));

        for ($loop = 0; $loop < $nb_loop; ++$loop) {
            $this->chapitre_debut($chapitre.'#'.$loop);
            for ($i = 0; $i < $nb; ++$i) {
                eval($phpcode);
            }

            $this->chapitre_fin($chapitre.'#'.$loop);
        }
    }

    /**
     * Exporte un chapitre au format CSV.
     *
     * @param string $filename
     * @return bool
     */
    public function export_chapitre($filename)
    {
        // Analyse des chapitres
        if (empty($this->time_chap)) {
            return false;
        }

        foreach ($this->time_chap as $id => $temp) {
            $calcul = $this->_vir($this->_conv($this->_unit($temp['fin']) - $this->_unit($temp['debut'])));
            error_log("{$id};{$calcul};ms\n", 3, $filename);
        }
    }
}

