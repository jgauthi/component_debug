<?php
/**
 * Timer - Stopwatch in milliseconds script time
    + the various steps in the code
 * @author: Jgauthi <github.com/jgauthi>
 * @maj 14/03/09
 * @version 1.0
 */

class timer
{
    var $debut;
    var $fin;
    var $time = array();
    var $start = false;
    var $header = 0;
    var $resultat;
    var $etape;

    //-- CONSTRUCTEUR ---------------------------------
    FUNCTION timer($header = 0)
    {
        $this -> debut = microtime();
        $this -> header = $header;
    }

    //-- ETALONNER LE PARCOURS DU SCRIPT --------------
    FUNCTION etape($nom)
    {
        $this -> time[] = array('nom' => $nom, 'time' => microtime());
    }

    //-- METTRE FIN AU COMPTEUR
    FUNCTION stop()
    {
        $this -> fin = microtime();

        // Conversion
        $this -> debut = $this -> _unit($this -> debut);
        $this -> fin = $this -> _unit($this -> fin);

        // Vérifie si le header est correcte
        if ($this -> header && headers_sent()) $this -> header = false;

        // Analyse des données
        $this -> resultat =  $this -> _conv($this -> fin - $this -> debut);

        // Analyse des étapes
        if (count($this -> time) > 0)
        {
            $etape = '';
            foreach($this -> time as $id => $tmp)
            {
                $temp = $this -> _vir($this -> _conv($this -> _unit($tmp['time']) - $this -> debut));
                $this -> etape .= '- '.$tmp['nom'].': '.$temp." ms\n";
                if ($this -> header)
                    header($id.'-Time: '.$temp.' ms -> '.$tmp['nom']);
            }
        }

        // Renvoi des données
        if ($this -> header)	header('X-Time: '.$this -> _vir($this -> resultat).' ms');
    }

    //--
    FUNCTION OutPut($type = 'commentaire')
    {
        if ($type == 'commentaire')
        {
            $av = '<!- TEMPS ECOULE DU SCRIPT: ';
            $ap = '-->';
        }
        else
        {
            $av = '<h2>Temps écoulé du script: </h2>';
            $ap = '';
        }

        if (count($this -> time) == 0)
            return ("\n\n".$av.'Time: '.$this -> _vir($this -> resultat).' ms'.$ap);
        else
            return "\n\n".$av.
                (($type != 'commentaire') ?
                    str_replace("\n","<br />\n", $this -> etape):
                    $this -> etape).
                $ap."\n". '=> Time: '.$this -> _vir($this -> resultat)." ms\n\n";
    }

    FUNCTION temps ()
    {
        return $this -> _vir($this -> resultat);
    }

    FUNCTION end($format = 'html')
    {
        $this->stop();
        echo $this->OutPut($format);
    }

    FUNCTION shutdown()
    {
        register_shutdown_function(array($this, 'end'));
    }

    //-- CONVERTISSEUR ---------------------------------
    FUNCTION _unit($time)
    {
        list($usec,$sec) = explode(' ',$time);
        return $usec + $sec;
    }

    FUNCTION _conv($time)
    {
        return substr($time*1000, 0, 6);

        // Récupérer les 6 premiers chiffres
        // Convertie en millisecondes
        // Remplace . par , ( écriture FR)
    }

    FUNCTION _vir($chiffre)
    {
        return str_replace('.', ',', $chiffre);
    }
}

?>