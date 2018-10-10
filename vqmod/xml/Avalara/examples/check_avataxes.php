<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tushar.sonje
 * Date: 2/12/14
 * Time: 12:12 PM
 * To change this template use File | Settings | File Templates.
 */

$a = session_id();
if(empty($a)) session_start();
if(isset($_SESSION['html'])){
    $html = $_SESSION['html'];
    $tp = new printhtml();
    $tp->print2pdf($html);
}


class printhtml{

    public function print2pdf($html){

        try{

        ob_clean();
        require_once('tcpdf_include.php');

// create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tushar');
        $pdf->SetTitle('Extension Conflict Checker');

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-15, PDF_MARGIN_RIGHT);

        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, array(255,102,0), array(255,102,0));
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));


// set some language-dependent strings (optional)
    //    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
     //       require_once(dirname(__FILE__).'/lang/eng.php');
     //       $pdf->setLanguageArray($l);
      //  }
//
// ---------------------------------------------------------

// set default font subsetting mode
   //     $pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 8, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

// set text shadow effect


// Set some content to print


// Print text using writeHTMLCell()
        $pdf->writeHTML($html, true, 0, true, 0);


// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
        ob_end_clean();
        $pdf->Output('AVALARA_001.pdf', 'I');
        }catch(Exception $e){
            session_destroy();
        }


    }



}

