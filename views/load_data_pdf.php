<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");

use setasign\Fpdi\Fpdi;

require_once('../vendor/setasign/fpdf/fpdf.php');

$doctorFunctions = new DoctorFunctions();
$userFunctions = new UsersFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$module = $_GET["module"];
$moduleDataID = base64_decode(strrev(urldecode(explode("/", $_GET["id"])[0])));
$pdfOptions = "";
switch ($module) {
    case "nursing-data":
        $moduleData = $doctorFunctions->getNursingDataByID($moduleDataID);
        $patientData = $doctorFunctions->getPatientDataByID($moduleData->person_id);
        _nursingData($patientData, $moduleData);
        break;
    case "medical-history":
        $moduleData = $doctorFunctions->getMedicalHistoryByID($moduleDataID);
        $patientData = $doctorFunctions->getPatientDataByID($moduleData->person_id);
        _medicalHistory($patientData, $moduleData);
        break;
    case "medical-consultation":
        $moduleData = $doctorFunctions->getMedicalConsultationByID($moduleDataID);
        $historyData = $doctorFunctions->getMedicalHistoryByID($moduleData->medicalhistory_id);
        $patientData = $doctorFunctions->getPatientDataByID($historyData->person_id);
        _medicalConsultation($patientData, $historyData, $moduleData);
        break;
    case "medical-evolve":
        $moduleData = $doctorFunctions->getMedicalEvolveByID($moduleDataID);
        $historyData = null;
        $patientData = null;
        if (!empty($moduleData)) {
            $historyData = $doctorFunctions->getMedicalHistoryByID($moduleData->medicalhistory_id);
            $patientData = $doctorFunctions->getPatientDataByID($historyData->person_id);
            $moduleData = $doctorFunctions->getMedicalEvolveByDate($moduleData->medicalhistory_id, $moduleData->date);
            if ($moduleData->num_rows == 0) {
                $moduleData = null;
            }
        }
        _medicalEvolve($patientData, $moduleData);
        break;
    case "medical-exam":
        $moduleData = $doctorFunctions->getMedicalExamByID($moduleDataID);
        $historyData = null;
        $patientData = null;
        if (!empty($moduleData)) {
            $historyData = $doctorFunctions->getMedicalHistoryByID($moduleData->medical_id);
            $patientData = $doctorFunctions->getPatientDataByID($historyData->person_id);
            $moduleData = $doctorFunctions->getMedicalExamByDate($moduleData->id, $moduleData->created_at);
            if ($moduleData->num_rows == 0) {
                $moduleData = null;
            }
        }
        _medicalExam($patientData, $moduleData);
        break;
    case "medical-diagnosis":
        $moduleData = $doctorFunctions->getMedicalDiagnosisByID($moduleDataID);
        $historyData = null;
        $patientData = null;
        if (!empty($moduleData)) {
            $historyData = $doctorFunctions->getMedicalHistoryByID($moduleData->medical_id);
            $patientData = $doctorFunctions->getPatientDataByID($historyData->person_id);
            $moduleData = $doctorFunctions->getMedicalDiagnosisByDate($moduleData->id, $moduleData->created_at);
            if ($moduleData->num_rows == 0) {
                $moduleData = null;
            }
        }
        _medicalExam($patientData, $moduleData);
        break;
    case "recipe":
        $moduleData = $doctorFunctions->getRecipeByID($moduleDataID);
        $detailsData = $doctorFunctions->getRecipeDetailsByID($moduleDataID);
        $patientData = $doctorFunctions->getPatientDataByID($moduleData->person_id);
        _recipeData($patientData, $detailsData, $moduleData);
        break;
    default:
        header("Location: /" . BASE_URL . "home");
        exit();
        break;
}

function readTemplate($file, $orientation = "P", $page = 1)
{
    try {
        $paperSize = ($orientation == "P") ? 210 : 297;
        $pdf = new FPDI();
        $pdf->AddPage($orientation);
        $pdf->setSourceFile($file);
        $tplIdx = $pdf->importPage($page);
        $pdf->useTemplate($tplIdx, 0, 0, $paperSize);
        return $pdf;
    } catch (\Throwable $th) {
        return null;
    }
}

function _nursingData($pt, $dt)
{
    if (empty($pt) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/nursing_data.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATA
            $pdf->SetXY(29, 30);
            $pdf->Write(0, date("d/m/Y H:i", strtotime($dt->created_at)));
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $pdf->SetXY(124, 30);
            $pdf->Write(0, utf8_decode($career));

            $pdf->SetXY(53, 36.8);
            $pdf->Write(0, utf8_decode($pt->name . " " . $pt->last_name));

            $pdf->SetXY(51, 43.7);
            $pdf->Write(0, utf8_decode($pt->identification));

            $pdf->SetXY(117, 43.7);
            $pdf->Write(0, utf8_decode(date_diff(date_create($pt->birth_date), date_create(date("Y-m-d")))->format("%y")));

            $pdf->SetXY(51, 50.8);
            $pdf->Write(0, utf8_decode(date("d/m/Y", strtotime($pt->birth_date))));

            $pdf->SetXY(128.5, 50.8);
            $pdf->Write(0, utf8_decode($pt->civil_state));

            $pdf->SetXY(33, 57.7);
            $pdf->Write(0, utf8_decode($pt->address));

            $pdf->SetXY(55, 64.5);
            $pdf->Write(0, utf8_decode($pt->phone));

            $pdf->SetXY(120.4, 78.2);
            $pdf->Write(0, utf8_decode($dt->height . " mts."));

            $pdf->SetXY(25.9, 78.2);
            $pdf->Write(0, utf8_decode($dt->weight . " kg."));

            $pdf->SetXY(23.1, 85.1);
            $pdf->Write(0, utf8_decode($dt->imc));

            $pdf->SetXY(31.5, 98.8);
            $pdf->Write(0, utf8_decode($dt->oxygen));

            $pdf->SetXY(130.6, 85.1);
            $pdf->Write(0, utf8_decode($dt->temperature . " ºC."));

            $pdf->SetXY(121.4, 98.8);
            $pdf->Write(0, utf8_decode($dt->pressure));

            $pdf->SetXY(149.4, 91.9);
            $pdf->Write(0, utf8_decode($dt->breathing_frequency));

            $pdf->SetXY(52.8, 91.9);
            $pdf->Write(0, utf8_decode($dt->heart_frequency));

            $pdf->Output('I', 'Datos de enfermeria - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _medicalHistory($pt, $dt)
{
    if (empty($pt) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/medical_history.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATA
            $pdf->SetXY(29, 30);
            $pdf->Write(0, date("d/m/Y H:i", strtotime($dt->created_at)));
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $pdf->SetXY(124, 30);
            $pdf->Write(0, utf8_decode($career));

            $pdf->SetXY(53, 36.8);
            $pdf->Write(0, utf8_decode($pt->name . " " . $pt->last_name));

            $pdf->SetXY(51, 43.7);
            $pdf->Write(0, utf8_decode($pt->identification));

            $pdf->SetXY(117, 43.7);
            $pdf->Write(0, utf8_decode(date_diff(date_create($pt->birth_date), date_create(date("Y-m-d")))->format("%y")));

            $pdf->SetXY(51, 50.8);
            $pdf->Write(0, utf8_decode(date("d/m/Y", strtotime($pt->birth_date))));

            $pdf->SetXY(128.5, 50.8);
            $pdf->Write(0, utf8_decode($pt->civil_state));

            $pdf->SetXY(33, 57.7);
            $pdf->Write(0, utf8_decode($pt->address));

            $pdf->SetXY(55, 64.5);
            $pdf->Write(0, utf8_decode($pt->phone));

            $pdf->SetXY(23.6, 78);
            $pdf->Write(0, utf8_decode($dt->app));

            $pdf->SetXY(23.6, 84.8);
            $pdf->Write(0, utf8_decode($dt->apf));

            $pdf->SetXY(24.4, 91.7);
            $pdf->Write(0, utf8_decode($dt->ago));

            $pdf->SetXY(32.5, 98.6);
            $pdf->Write(0, utf8_decode($dt->allergies));

            $pdf->SetXY(121.2, 98.6);
            $pdf->Write(0, utf8_decode($dt->habits));

            $pdf->SetXY(21.8, 105.4);
            $pdf->Write(0, utf8_decode($dt->pressure));

            $pdf->SetXY(58.2, 105.4);
            $pdf->Write(0, utf8_decode($dt->heart_frequency));

            $pdf->SetXY(98.3, 105.4);
            $pdf->Write(0, utf8_decode($dt->weight));

            $pdf->SetXY(135.9, 105.4);
            $pdf->Write(0, utf8_decode($dt->height));

            $pdf->SetXY(168.1, 105.4);
            $pdf->Write(0, utf8_decode($dt->imc));

            $pdf->Output('I', 'Antecedentes (Medicina) - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _medicalConsultation($pt, $ht, $dt)
{
    if (empty($pt) || empty($ht) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/medical_history.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATOS PACIENTE
            $pdf->SetXY(29, 30);
            $pdf->Write(0, date("d/m/Y H:i", strtotime($dt->created_at)));
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $pdf->SetXY(124, 30);
            $pdf->Write(0, utf8_decode($career));

            $pdf->SetXY(53, 36.8);
            $pdf->Write(0, utf8_decode($pt->name . " " . $pt->last_name));

            $pdf->SetXY(51, 43.7);
            $pdf->Write(0, utf8_decode($pt->identification));

            $pdf->SetXY(117, 43.7);
            $pdf->Write(0, utf8_decode(date_diff(date_create($pt->birth_date), date_create(date("Y-m-d")))->format("%y")));

            $pdf->SetXY(51, 50.8);
            $pdf->Write(0, utf8_decode(date("d/m/Y", strtotime($pt->birth_date))));

            $pdf->SetXY(128.5, 50.8);
            $pdf->Write(0, utf8_decode($pt->civil_state));

            $pdf->SetXY(33, 57.7);
            $pdf->Write(0, utf8_decode($pt->address));

            $pdf->SetXY(55, 64.5);
            $pdf->Write(0, utf8_decode($pt->phone));

            //DATOS HISTORIAL

            $pdf->SetXY(23.6, 78);
            $pdf->Write(0, utf8_decode($ht->app));

            $pdf->SetXY(23.6, 84.8);
            $pdf->Write(0, utf8_decode($ht->apf));

            $pdf->SetXY(24.4, 91.7);
            $pdf->Write(0, utf8_decode($ht->ago));

            $pdf->SetXY(32.5, 98.6);
            $pdf->Write(0, utf8_decode($ht->allergies));

            $pdf->SetXY(121.2, 98.6);
            $pdf->Write(0, utf8_decode($ht->habits));

            $pdf->SetXY(21.8, 105.4);
            $pdf->Write(0, utf8_decode($ht->pressure));

            $pdf->SetXY(58.2, 105.4);
            $pdf->Write(0, utf8_decode($ht->heart_frequency));

            $pdf->SetXY(98.3, 105.4);
            $pdf->Write(0, utf8_decode($ht->weight));

            $pdf->SetXY(135.9, 105.4);
            $pdf->Write(0, utf8_decode($ht->height));

            $pdf->SetXY(168.1, 105.4);
            $pdf->Write(0, utf8_decode($ht->imc));

            //DATOS ANAMNESIS

            $pdf->SetMargins(17, 0);
            $pdf->SetXY(17, 119.6);
            //$t = wordwrap(utf8_decode($dt->reason), 115);
            $pdf->Write(3, utf8_decode($dt->reason));

            $pdf->SetXY(45.2, 149.6);
            $pdf->Write(3, utf8_decode($dt->head_neck));

            $pdf->SetXY(63.8, 157.3);
            $pdf->Write(3, utf8_decode($dt->thorax));

            $pdf->SetXY(32.8, 164.6);
            $pdf->Write(3, utf8_decode($dt->abdomen));

            $pdf->SetXY(40.6, 172.5);
            $pdf->Write(3, utf8_decode($dt->extremities));

            $pdf->SetXY(54.6, 180.1);
            $pdf->Write(3, utf8_decode($dt->diagnostic));

            $pdf->SetXY(17, 191.5);
            $pdf->Write(3, utf8_decode($dt->treatment));

            $pdf->Output('I', 'Anamnesis y Examen Físico (Medicina) - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _medicalEvolve($pt, $dt)
{
    if (empty($pt) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/medical_evolve.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATOS PACIENTE
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $apellidos = explode(" ", $pt->last_name);

            $limitBoxes = 29;
            $countBoxes = 0;
            $addPageBand = false;
            do {
                $countBoxes = 0;
                if ($addPageBand) {
                    $pdf->AddPage("P");
                    $tplIdx = $pdf->importPage(1);
                    $pdf->useTemplate($tplIdx, 0, 0, 210);
                }

                $pdf->SetFontSize(9);
                $pdf->SetXY(125, 25.4);
                $pdf->Write(0, utf8_decode($career));

                $pdf->SetXY(16.8, 33.5);
                $pdf->Write(0, utf8_decode($apellidos[0]));
                $pdf->SetXY(61, 33.5);
                $pdf->Write(0, (isset($apellidos[1])) ? utf8_decode($apellidos[1]) : "");
                $pdf->SetXY(92.7, 33.5);
                $pdf->Write(0, utf8_decode($pt->name));
                $pdf->SetXY(149.6, 33.5);
                $pdf->Write(0, $pt->identification);

                //DATOS EVOLUCIÓN

                $count = 0;
                $pdf->SetFontSize(7);
                while ($r = $dt->fetch_object()) {
                    $countBoxes = $countBoxes + 1;
                    $coordY = 7.5;

                    $pdf->SetMargins(17, 0);
                    $pdf->SetXY(17, 53.3 + ($coordY * $count));
                    $pdf->Write(0, date("d/m/Y", strtotime($r->date)));

                    $pdf->SetMargins(39.1, 0, 81);
                    $pdf->SetXY(39.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->evolve_notes));

                    $pdf->SetMargins(133.1, 0, 17);
                    $pdf->SetXY(133.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->prescription));

                    $count = $count + 1;
                    if ($countBoxes == $limitBoxes) {
                        $addPageBand = true;
                        break;
                    }
                }
            } while ($countBoxes == $limitBoxes);

            $pdf->Output('I', 'Evolución (Medicina) - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _recipeData($pt, $dt, $mt)
{
    if (empty($pt) || empty($dt) || empty($mt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;
    global $userFunctions;

    $doctor = $userFunctions->getUserByID($mt->created_by);
    $doctorArea = $userFunctions->getAreaByUserID($mt->created_by);
    if (empty($doctor) || empty($doctorArea)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }

    $pdf = readTemplate("../assets/formats/recipe.pdf", "L");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DOCTOR DATA
            $abbrv = ($doctor->sex == "M") ? "Dr." : "Dra.";
            //$pdf->SetXY(29, 30);
            //$pdf->Write(0, utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name));
            $pdf->Text(72.6 - ($pdf->GetStringWidth(utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name)) / 2), 20.3, utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name));
            $pdf->Text(218.2 - ($pdf->GetStringWidth(utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name)) / 2), 20.3, utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name));

            $pdf->Text(72.6 - ($pdf->GetStringWidth(strtoupper(utf8_decode("Área de ".$doctorArea->name))) / 2), 24.2, strtoupper(utf8_decode("Área de ".$doctorArea->name)));
            $pdf->Text(218.2 - ($pdf->GetStringWidth(strtoupper(utf8_decode("Área de ".$doctorArea->name))) / 2), 24.2, strtoupper(utf8_decode("Área de ".$doctorArea->name)));

            $pdf->Text(79 - ($pdf->GetStringWidth(utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name)) / 2), 190.5, utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name));
            $pdf->Text(228.6 - ($pdf->GetStringWidth(utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name)) / 2), 190.5, utf8_decode($abbrv." ".$doctor->name." ".$doctor->last_name));

            //DATA
            $pdf->SetXY(38.9, 36.1);
            $pdf->Write(0, date("d/m/Y", strtotime($mt->created_at)));
            $pdf->SetXY(186.9, 36.1);
            $pdf->Write(0, date("d/m/Y", strtotime($mt->created_at)));

            $pdf->SetXY(36.8, 41.1);
            $pdf->Write(0, utf8_decode($pt->name." ".$pt->last_name));
            $pdf->SetXY(185.7, 41.1);
            $pdf->Write(0, utf8_decode($pt->name." ".$pt->last_name));
            
            $count = 0;
            while ($r = $dt->fetch_object()) {
                $coordY = 9;
                $productName = $doctorFunctions->getProductByID($r->product);
                if (empty($productName)) {
                    $productName = $r->product;
                } else {
                    $productName = $productName->name;
                }

                //$pdf->SetFontSize(9);
                $pdf->SetMargins(17, 0, 131);
                $pdf->SetXY(17, 53.5 + ($coordY * $count));
                $pdf->MultiCell(125, 3, utf8_decode($r->quantity." - ".$productName." ".(($r->kit_quantity > 0) ? "- (se entrega ".$r->kit_quantity.")" : "")));

                //$pdf->SetFontSize(7);
                $pdf->SetMargins(165.5, 0);
                $pdf->SetXY(165.5, 53.5 + ($coordY * $count));
                $pdf->MultiCell(125, 3, utf8_decode($r->indications));

                $count = $count + 1;
            }

            $pdf->Output('I', 'Receta - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _medicalExam($pt,$dt)
{
    if (empty($pt) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/medical_exam.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATOS PACIENTE
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $apellidos = explode(" ", $pt->last_name);

            $limitBoxes = 29;
            $countBoxes = 0;
            $addPageBand = false;
            do {
                $countBoxes = 0;
                if ($addPageBand) {
                    $pdf->AddPage("P");
                    $tplIdx = $pdf->importPage(1);
                    $pdf->useTemplate($tplIdx, 0, 0, 210);
                }

                $pdf->SetFontSize(9);
                $pdf->SetXY(125, 25.4);
                $pdf->Write(0, utf8_decode($career));

                $pdf->SetXY(16.8, 33.5);
                $pdf->Write(0, utf8_decode($apellidos[0]));
                $pdf->SetXY(61, 33.5);
                $pdf->Write(0, (isset($apellidos[1])) ? utf8_decode($apellidos[1]) : "");
                $pdf->SetXY(92.7, 33.5);
                $pdf->Write(0, utf8_decode($pt->name));
                $pdf->SetXY(149.6, 33.5);
                $pdf->Write(0, $pt->identification);





                //DATOS Examen

                $count = 0;
                $pdf->SetFontSize(7);
                while ($r = $dt->fetch_object()) {
                    $countBoxes = $countBoxes + 1;
                    $coordY = 7.5;
                    $pdf->SetXY(16.8, 41);
                    $pdf->Write(0, "FECHA: ".date("d/m/Y", strtotime($r->fecha)));
                    $pdf->SetMargins(17, 0);
                    $pdf->SetXY(17, 53.3 + ($coordY * $count));
                    $num = $count+1;
                    $pdf->Write(0, "".$num);

                    $pdf->SetMargins(39.1, 0, 81);
                    $pdf->SetXY(39.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->exam));

                    $pdf->SetMargins(133.1, 0, 17);
                    $pdf->SetXY(133.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->category));

                    $count = $count + 1;
                    if ($countBoxes == $limitBoxes) {
                        $addPageBand = true;
                        break;
                    }
                }
            } while ($countBoxes == $limitBoxes);


            $pdf->Output('I', 'Examen (Medicina) - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}

function _medicalDiagnosis($pt,$dt)
{
    if (empty($pt) || empty($dt)) {
        header("Location: /" . BASE_URL . "home");
        exit();
    }
    global $doctorFunctions;

    $pdf = readTemplate("../assets/formats/medical_diagnosis.pdf");
    $isGenerated = true;
    if (!empty($pdf)) {
        try {
            //$pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont("Helvetica");
            $pdf->SetFontSize(9);

            //DATOS PACIENTE
            $career = "";
            if (!empty($pt->user_id)) {
                $career = $doctorFunctions->getPatientTypeByUserID($pt->user_id);
                if (!str_contains($career, "Estudiante - ")) {
                    $career = "";
                } else {
                    $career = str_replace("Estudiante - ", "", $career);
                }
            }
            $apellidos = explode(" ", $pt->last_name);

            $limitBoxes = 29;
            $countBoxes = 0;
            $addPageBand = false;
            do {
                $countBoxes = 0;
                if ($addPageBand) {
                    $pdf->AddPage("P");
                    $tplIdx = $pdf->importPage(1);
                    $pdf->useTemplate($tplIdx, 0, 0, 210);
                }

                $pdf->SetFontSize(9);
                $pdf->SetXY(125, 25.4);
                $pdf->Write(0, utf8_decode($career));

                $pdf->SetXY(16.8, 33.5);
                $pdf->Write(0, utf8_decode($apellidos[0]));
                $pdf->SetXY(61, 33.5);
                $pdf->Write(0, (isset($apellidos[1])) ? utf8_decode($apellidos[1]) : "");
                $pdf->SetXY(92.7, 33.5);
                $pdf->Write(0, utf8_decode($pt->name));
                $pdf->SetXY(149.6, 33.5);
                $pdf->Write(0, $pt->identification);


                $pdf->SetXY(21.8, 43.5);
                $pdf->Write(0, date("d/m/Y", strtotime($dt->fecha)));

                //DATOS Examen

                $count = 0;
                $pdf->SetFontSize(7);
                while ($r = $dt->fetch_object()) {
                    $countBoxes = $countBoxes + 1;
                    $coordY = 7.5;



                    $pdf->SetMargins(39.1, 0, 81);
                    $pdf->SetXY(39.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->description));

                    $pdf->SetMargins(133.1, 0, 17);
                    $pdf->SetXY(133.1, 50.5 + ($coordY * $count));
                    $pdf->Write(3, utf8_decode($r->cie10));

                    $count = $count + 1;
                    if ($countBoxes == $limitBoxes) {
                        $addPageBand = true;
                        break;
                    }
                }
            } while ($countBoxes == $limitBoxes);

            $pdf->Output('I', 'Examen (Medicina) - ' . $pt->name . ' ' . $pt->last_name . '.pdf');
        } catch (\Throwable $th) {
            $isGenerated = false;
        }
    } else {
        $isGenerated = false;
    }

    if (!$isGenerated) {
        echo "<center><h3>Error al generar archivo... Intente más tarde.</h3></center>";
    }
}
