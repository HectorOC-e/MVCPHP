<?php
/**
 * PHP Version 7.2
 * Mnt
 *
 * @category Controller
 * @package  Controllers\Mnt
 * @author   Orlando J Betancourth <orlando.betancourth@gmail.com>
 * @license  Comercial http://
 * @version  CVS:1.0.0
 * @link     http://url.com
 */
 namespace Controllers\Mnt;

// ---------------------------------------------------------------
// Sección de imports
// ---------------------------------------------------------------
use Controllers\PublicController;
use Views\Renderer;
use Utilities\Validators;
use Dao\Mnt\Scores;

/**
 * Producto
 *
 * @category Public
 * @package  Controllers\Mnt;
 * @author   Orlando J Betancourth <orlando.betancourth@gmail.com>
 * @license  MIT http://
 * @link     http://
 */
class Score extends PublicController
{
    private $viewData = array();
    private $arrModeDesc = array();
    private $arrEstados = array();

    /**
     * Runs the controller
     *
     * @return void
     */
    public function run():void
    {
        // code
        $this->init();
        // Cuando es método GET (se llama desde la lista)
        if (!$this->isPostBack()) {
            $this->procesarGet();
        }
        // Cuando es método POST (click en el botón)
        if ($this->isPostBack()) {
            $this->procesarPost();
        }
        // Ejecutar Siempre
        $this->processView();
        Renderer::render('mnt/score', $this->viewData);
    }

    private function init()
    {
        $this->viewData = array();
        $this->viewData["mode"] = "";
        $this->viewData["mode_desc"] = "";
        $this->viewData["crsf_token"] = "";

        $this->viewData["scoreid"] = "";
        $this->viewData["scoredsc"] = "";
        $this->viewData["error_scoredsc"] = array();
        $this->viewData["scoreauthor"] = "";
        $this->viewData["error_scoreauthor"] = array();
        $this->viewData["scoregenre"] = "";
        $this->viewData["error_scoregenre"] = array();
        $this->viewData["scoreyear"] = "";
        $this->viewData["error_scoreyear"] = array();
        $this->viewData["scoresales"] = "";
        $this->viewData["error_scoresales"] = array();
        $this->viewData["scoreprice"] = "";
        $this->viewData["error_scoreprice"] = array();
        $this->viewData["scoredocurl"] = "";
        $this->viewData["error_scoredocurl"] = array();
        $this->viewData["scoreest"] = "";
        $this->viewData["scoreestArr"] = array();

        $this->viewData["btnEnviarText"] = "Guardar";
        $this->viewData["readonly"] = false;
        $this->viewData["showBtn"] = true;

        $this->arrModeDesc = array(
            "INS"=>"Nuevo Score",
            "UPD"=>"Editando %s %s",
            "DSP"=>"Detalle de %s %s",
            "DEL"=>"Eliminado %s %s"
        );

        $this->arrEstados = array(
            array("value" => "ACT", "text" => "Activo"),
            array("value" => "INA", "text" => "Inactivo"),
        );


        $this->viewData["scoreestArr"] = $this->arrEstados;

    }

    private function procesarGet()
    {
        if (isset($_GET["mode"])) {
            $this->viewData["mode"] = $_GET["mode"];
            if (!isset($this->arrModeDesc[$this->viewData["mode"]])) {
                error_log('Error: (Score) Modo solicitado no existe.');
                \Utilities\Site::redirectToWithMsg(
                    "index.php?page=mnt_scores",
                    "No se puede procesar su solicitud!"
                );
            }
        }
        if ($this->viewData["mode"] !== "INS" && isset($_GET["id"])) {
            $this->viewData["scoreid"] = intval($_GET["id"]);
            $tmpScore = Scores::getById($this->viewData["scoreid"]);
            error_log(json_encode($tmpScore));
            \Utilities\ArrUtils::mergeFullArrayTo($tmpScore, $this->viewData);
        }
    }
    private function procesarPost()
    {
        // Validar la entrada de Datos
        //  Todos valor puede y sera usando en contra del sistema
        $hasErrors = false;
        \Utilities\ArrUtils::mergeArrayTo($_POST, $this->viewData);
        if (isset($_SESSION[$this->name . "crsf_token"])
            && $_SESSION[$this->name . "crsf_token"] !== $this->viewData["crsf_token"]
        ) {
            \Utilities\Site::redirectToWithMsg(
                "index.php?page=mnt_scores",
                "ERROR: Algo inesperado sucedió con la petición Intente de nuevo."
            );
        }

        if (Validators::IsEmpty($this->viewData["scoredsc"])) {
            $this->viewData["error_scoredsc"][]
                = "La descripción es requerida";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoreauthor"])) {
            $this->viewData["error_scoreauthor"][]
                = "El autor es requerido";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoregenre"])) {
            $this->viewData["error_scoregenre"][]
                = "El genero es requerido";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoreyear"])) {
            $this->viewData["error_scoreyear"][]
                = "El año es requerido";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoresales"])) {
            $this->viewData["error_scoresales"][]
                = "La cantidad vendida es requerida";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoreprice"])) {
            $this->viewData["error_scoreprice"][]
                = "El precio es requerido";
            $hasErrors = true;
        }
        if (Validators::IsEmpty($this->viewData["scoredocurl"])) {
            $this->viewData["error_scoredocurl"][]
                = "La URL es requerida";
            $hasErrors = true;
        }
        error_log(json_encode($this->viewData));
        // Ahora procedemos con las modificaciones al registro
        if (!$hasErrors) {
            $result = null;
            switch($this->viewData["mode"]) {
            case 'INS':
                $result = Scores::insert(
                    $this->viewData["scoredsc"],
                    $this->viewData["scoreauthor"],
                    $this->viewData["scoregenre"],
                    $this->viewData["scoreyear"],
                    $this->viewData["scoresales"],
                    $this->viewData["scoreprice"],
                    $this->viewData["scoredocurl"],
                    $this->viewData["scoreest"]
                );
                if ($result) {
                        \Utilities\Site::redirectToWithMsg(
                            "index.php?page=mnt_scores",
                            "Score Guardado Satisfactoriamente!"
                        );
                }
                break;
            case 'UPD':
                $result = Scores::update(
                    $this->viewData["scoredsc"],
                    $this->viewData["scoreauthor"],
                    $this->viewData["scoregenre"],
                    intVal($this->viewData["scoreyear"]),
                    intVal($this->viewData["scoresales"]),
                    floatVal($this->viewData["scoreprice"]),
                    $this->viewData["scoredocurl"],
                    $this->viewData["scoreest"],
                    intVal($this->viewData["scoreid"])
                );
                if ($result) {
                    \Utilities\Site::redirectToWithMsg(
                        "index.php?page=mnt_scores",
                        "Score Actualizado Satisfactoriamente"
                    );
                }
                break;
            case 'DEL':
                $result = Scores::delete(
                    intval($this->viewData["scoreid"])
                );
                if ($result) {
                    \Utilities\Site::redirectToWithMsg(
                        "index.php?page=mnt_scores",
                        "Score Eliminado Satisfactoriamente"
                    );
                }
                break;
            }
        }
    }

    private function processView()
    {
        if ($this->viewData["mode"] === "INS") {
            $this->viewData["mode_desc"]  = $this->arrModeDesc["INS"];
            $this->viewData["btnEnviarText"] = "Guardar Nuevo";
        } else {
            $this->viewData["mode_desc"]  = sprintf(
                $this->arrModeDesc[$this->viewData["mode"]],
                $this->viewData["scoreid"],
                $this->viewData["scoredsc"]
            );

            $this->viewData["scoreestArr"]
                = \Utilities\ArrUtils::objectArrToOptionsArray(
                    $this->arrEstados,
                    'value',
                    'text',
                    'value',
                    $this->viewData["scoreest"]
                );

            if ($this->viewData["mode"] === "DSP") {
                $this->viewData["readonly"] = true;
                $this->viewData["showBtn"] = false;
            }
            if ($this->viewData["mode"] === "DEL") {
                $this->viewData["readonly"] = true;
                $this->viewData["btnEnviarText"] = "Eliminar";
            }
            if ($this->viewData["mode"] === "UPD") {
                $this->viewData["btnEnviarText"] = "Actualizar";
            }
        }
        $this->viewData["crsf_token"] = md5(getdate()[0] . $this->name);
        $_SESSION[$this->name . "crsf_token"] = $this->viewData["crsf_token"];
    }
}
