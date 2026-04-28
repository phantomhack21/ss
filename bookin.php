<?php
	ini_set('memory_limit', '1024M');
	include_once('config.php');
	include_once('database.php');
	include_once('functions.php');
	include_once('class/XMLForms.php');
	include_once('class/XMLDraw.php');
	require_once('class/PHPExcel/IOFactory.php');
	require_once('class/PHPExcel.php');
	include_once('class/Journals.php');

//    include('googleauth/config.php');
//    include('googleauth/class/userClass.php');
//    $userClass = new userClass();
//    $userDetails=$userClass->userDetails($_SESSION['uid']);
//
//    include('googleauth/session.php');
//    $userDetails=$userClass->userDetails($session_uid);


	error_reporting(0);
	$dlFile = false;
	$alert = false;
	$upload = false;
	$form = [];

	/**
	 * bob - Some step code in article stage is in $_GET['stepCode']
	 * article - adjusted stepCode in URL
	 **/ 	
	$stepCode = (!empty($_GET['stepCode']))? $_GET['stepCode']: "";
	$manuscript = (!empty($_POST['Number']))? $_POST['Number']: "";

	if(isset($_GET['issSubmit']) && !empty($_POST['Issue_Code'])) {
		$stepCode = $_POST['Workflow_Step'];
		$issue = "{$_POST['Journal_Code']}-{$_POST['Volume_Code']}-{$_POST['Issue']}";
		$workflows    = Workflow::getByJournalCode($issue); 	
		$nexStepCodes = Workflow::getNextStepCode($issue, true);
		$nexStepCodes = array_keys($nexStepCodes);
	} else {
		$workflows    = Workflow::getByArticleCode($manuscript); 
		$nexStepCodes = Workflow::getNextStepCode($manuscript);
		$nexStepCodes = array_keys($nexStepCodes);
	}




	if(isset($_FILES['filesToUpload'])) {
		$OpenFile = "Article_".rand(10,99999)."_".$_FILES['filesToUpload']['name'];
		$target_path = "excel/";
		$target_path = $target_path .basename($OpenFile);
		
		if(move_uploaded_file($_FILES['filesToUpload']['tmp_name'], $target_path)) {
			$arrExcel = [];
			$arrIn = 0;
			$inputFileName = "excel/".$OpenFile; 

			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);  
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
			$objReader->setReadDataOnly(true);
			
			/**  Load $inputFileName to a PHPExcel Object  **/  
			$objPHPExcel = $objReader->load($inputFileName); 
			$objReader->setReadDataOnly(true);

			$total_sheets=$objPHPExcel->getSheetCount(); // here 4  
			$allSheetName=$objPHPExcel->getSheetNames(); // array ([0]=>'student',[1]=>'teacher',[2]=>'school',[3]=>'college')  
			$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
			$highestRow = $objWorksheet->getHighestRow();
			$totalcount= 2;
			
			for($i=$totalcount; $i<=$highestRow;$i++) {
				$arrExcel[$arrIn]['doi'] = $objPHPExcel->getActiveSheet(0)->getCell('C'.$i)->getValue();
				$arrExcel[$arrIn]['start_page'] = $objPHPExcel->getActiveSheet(0)->getCell('D'.$i)->getValue();
				$arrExcel[$arrIn]['end_page'] = $objPHPExcel->getActiveSheet(0)->getCell('E'.$i)->getValue();
				$arrIn++;
			}
			
			if(!empty($arrExcel)) {
				$x = new XMLDraw($arrExcel);
				$xml = $x->drawIPS();
				file_put_contents(TEMP_FOLDER."Manuscript_in_issue_page.xml", $xml);
				$file = "Manuscript_in_issue_page.xml";
				$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
				$ulFile = '<a href="upload.php?isv1=true&filename='.$file.'&xml='.$issue.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
				$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
				$alert[] = $dlFile;
				$alert[] = 3;
				$alert[4] = $ulFile;
				$alert[5] = $vwFile;
			}
			
			
		}
	}
	
	if(isset($_REQUEST['iss'])) {
		$xmlForm = "xml/Issues.xml";
	} else if(isset($_REQUEST['upl'])) {
		
	} else {
		$xmlForm = "xml/BookIn.xml";
	}
	
	function xml2multiarray($xml){
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $xml, $xmlarray);
		$opened = array();
		$array = array();
		$arrsize = sizeof($xmlarray);
		for($j=0;$j<$arrsize;$j++) {
			$val = $xmlarray[$j];
			switch($val["type"]) {
				case "open":
					$opened[$val["tag"]] = $array;
					unset($array);
					break;
				case "complete":
					$array[$val["tag"]][] = $val["value"];
				break;
				case "close":
					$closed = $opened[$val["tag"]];
					$closed[$val["tag"]] = $array;
					$array = $closed;
				break;
			}
		}
		return $array;
	}
	
	if (isset($_REQUEST['BookIn']) && $_REQUEST['BookIn'] == True) {
		
		$x = new XMLDraw($_REQUEST);
		$xml = $x->drawBookIn();
		file_put_contents(TEMP_FOLDER.$xml[1].".xml", $xml[0]);
		$file = $xml[1].".xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode=bookin">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;

	}
	
	
	
	
	if (isset($_REQUEST['issSubmit']) && $_REQUEST['issSubmit'] == true) {
		
		if (($_REQUEST['Workflow_Step'] == 'IssueApprovedForPublication' || $_REQUEST['Workflow_Step'] == 'IssuePaginated' || $_REQUEST['Workflow_Step'] == 'IssueToPrinter')
			&& empty($_REQUEST['FirstPaginatedPage']) 
			&& empty($_REQUEST['LastPaginatedPage'])
			&& empty($_REQUEST['TotalPaginatedPages'])
			&& empty($_REQUEST['PreliminaryPages'])
			&& empty($_REQUEST['PostliminaryPages'])
			&& empty($_REQUEST['TotalPages'])
			&& empty($_REQUEST['AdPages'])
		) {
		
			echo '<script language="javascript">';
			echo 'alert("Cannot Submit Incomplete Form with Workflow Step ['.$_REQUEST['Workflow_Step'].'].")';
			echo '</script>';
			dd();
		}
		
		
		$x = new XMLDraw($_REQUEST);
		$xml = $x->drawIssue();

		file_put_contents(TEMP_FOLDER.$xml[1].".xml", $xml[0]);
		$file = $xml[1].".xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?isv1=true&filename='.$file.'&xml='.$issue.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
		
	}
	
	if (isset($_REQUEST['proofTo']) && !empty($_REQUEST['proofTo'])) {
		
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawProofTo($xmlData);
		file_put_contents(TEMP_FOLDER.$_REQUEST['proofTo']."-ProofToProofReader.xml", $file);
		$file = $_REQUEST['proofTo']."-ProofToProofReader.xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}
	
	if (isset($_REQUEST['proofFrom']) && !empty($_REQUEST['proofFrom'])) {
		
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawProofFrom($xmlData);
		file_put_contents(TEMP_FOLDER.$_REQUEST['proofFrom']."-ProofCorrexFromProofreader.xml", $file);
		$file = $_REQUEST['proofFrom']."-ProofCorrexFromProofreader.xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}
	
	if (isset($_REQUEST['doi']) && !empty($_REQUEST['doi'])) {
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawUpdatePageDetails($xmlData);
		file_put_contents(TEMP_FOLDER.$_REQUEST['doi']."-updatepagedetails.xml", $file);
		$file = $_REQUEST['doi']."-updatepagedetails.xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}
	
	if (isset($_REQUEST['wfs']) && !empty($_REQUEST['wfs'])) {
		$x = new XMLDraw($_REQUEST);
		$mod = $x->updateWorkflowStepFromManuscript($_REQUEST['xml'], $_REQUEST['stepCode']);
		
		$xmlData = $x->getXMLTree($_REQUEST['xml']);

		if (!empty($_REQUEST['date'])) {
			$xmlData['dateEntered'] = $_REQUEST['date'];
		}
		$file = $x->drawUpdateWorkflowStep($xmlData);
		file_put_contents(TEMP_FOLDER.$_REQUEST['wfs']."-".$_REQUEST['stepCode'].".xml", $file);
		$file = $_REQUEST['wfs']."-".$_REQUEST['stepCode'].".xml";
		
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}
	
	/* 	added issue */
	
	if (isset($_REQUEST['iss']) && !empty($_REQUEST['iss'])) {
		$x = new XMLDraw($_REQUEST);
		$issueType = $_REQUEST['stepCode'];
		$mod = $x->updateWorkflowStepFromManuscript($_REQUEST['xml'], $_REQUEST['stepCode']);
		
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawIssue($xmlData, $issueType);
		file_put_contents(TEMP_FOLDER.$_REQUEST['iss']."-".$_REQUEST['stepCode'].".xml", $file);
		$file = $_REQUEST['iss']."-".$_REQUEST['stepCode'].".xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Issue Step XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
	}
	
	if (isset($_REQUEST['fig']) && !empty($_REQUEST['fig'])) {
		//bob -  step = ManuscriptApproved hardcoded in drawUpdateFigureInfo
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawUpdateFigureInfo($xmlData);
		file_put_contents(TEMP_FOLDER.$_REQUEST['fig']."-ManuscriptApproved.xml", $file);
		$file = $_REQUEST['fig']."-ManuscriptApproved.xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'&xml='.$manuscript.'&stepCode='.$stepCode.'">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}

	/* 	THIS IS OLD FIGURE INFO BUTTON
	if (isset($_REQUEST['fig']) && !empty($_REQUEST['fig'])) {
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		$file = $x->drawUpdateFigureInfo($xmlData);
		file_put_contents($_REQUEST['fig']."-updatefiguredetails.xml", $file);
		$file = $_REQUEST['fig']."-updatefiguredetails.xml";
		$dlFile = '<a href="download.php?filename='.$file.'" target="_blank">Download Generated XML</a>';
		$ulFile = '<a href="upload.php?filename='.$file.'" target="_blank">Upload Generated XML to UK-FTP</a>';
		$vwFile = '<a href="view.php?filename='.$file.'" target="_blank">View Generated XML</a>';
		$alert[] = $dlFile;
		$alert[] = 3;
		$alert[4] = $ulFile;
		$alert[5] = $vwFile;
	}
	*/

	
	if (isset($_REQUEST['updateManuscript']) && $_REQUEST['updateManuscript'] == "updateManuscriptSubmit") {
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		
		$file = $x->drawUpdateManuscript($_REQUEST);
		
		if ($file == 'incorrect') {
			$alert[] = "WRONG PASSWORD! Call Admin Immediately.";
			$alert[] = 1;
			
		} else {
			
			$backUpFile = $x->backUpFile($_REQUEST['xml']);
			if (!$backUpFile) {
				$alert[] = "Backing Up File Failed";
				$alert[] = 1;
			} else {
				$alert[] = "Backing Up File Success";
				$alert[] = 2;
			}
			
			#$file = $x->drawUpdateManuscript($_REQUEST);
			
			if ($file == 'incorrect') {
				$alert[] = "WRONG PASSWORD! Call Admin Immediately.";
				$alert[] = 1;
			} else {
				file_put_contents('xml/'.$_REQUEST['xml'].".xml", $file);
				$backUpFile = $x->backUpFile($_REQUEST['xml']);
			}
		}
		
	}
	
	
	
	
	if (isset($_REQUEST['xml']) && !empty($_REQUEST['xml'])) {
		$x = new XMLDraw($_REQUEST);
		$xmlData = $x->getXMLTree($_REQUEST['xml']);
		
		if (empty($xmlData)) {
			$alert[] = "Manuscript did not exist!";
			$alert[] = 1;
		} else {
			$alert[] = "Manuscript <b>".$_REQUEST['xml']."</b> opened.";
			$alert[] = 4;
			$form= $x->drawManuscriptForm($xmlData);
			$xmlForm = $form[0];
		}
	}
	
	#	UPLOAD IPS
	if (isset($_REQUEST['upl']) && $_REQUEST['upl'] == true) {
		$xmlForm = "";
		$upload = true;
		$files = scandir(getcwd().'\xml',1);
		
		$i = 0;
		foreach ($files as $tmp) {
			
			switch($i) {
				case 0:
					$a1[] = $tmp;
					$i = 1;
					break;
				case 1:
					$a2[] = $tmp;
					$i = 2;
					break;
				case 2:
					$a3[] = $tmp;
					$i = 0;
					break;
				default:
					break;
			}
		}
		
		
		if(isset($_FILES['filesToUpload']) && count($_FILES['filesToUpload'])) {

			foreach ($_FILES['filesToUpload'] as $file) {
				#move_uploaded_file($,"\xml\\".$file_name);
				#dump($ind);
			}
		}
		
	}
	
?>

<head>
	<link rel="stylesheet" href="style/bootstrap.min.css" />
	<link rel="stylesheet" href="style/bootstrap-datetimepicker.min.css" />

	<script src="script/jquery.min.js"></script>
	<script src="script/moment.min.js"></script>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<link rel="stylesheet" type="text/css" href="dhtmlx/codebase/fonts/font_roboto/roboto.css"/>
	<link rel="stylesheet" type="text/css" href="dhtmlx/codebase/dhtmlxform.css"/>
	<script src="dhtmlx/codebase/dhtmlxform.js"></script>
	<link rel="stylesheet" href="style/style.css">	
	
	
	<!--	For Datepicker	-->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<!--<link rel="stylesheet" href="/resources/demos/style.css">-->
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  

	
</head>



<!DOCTYPE html>
<html lang="en">
<head>

  <title>Swift XML</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <style>
  input[name=xml] {
    width: 230px;
    box-sizing: border-box;
    border: 2px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    background-color: white;
    background-image: url('image/searchicon.png');
    background-position: 10px 10px; 
    background-repeat: no-repeat;
    padding: 12px 20px 12px 40px;
    -webkit-transition: width 0.4s ease-in-out;
    transition: width 0.4s ease-in-out;
}

input[type=text]:focus {
    width: 100%;
}
    /* Remove the navbar's default margin-bottom and rounded borders */ 
    .navbar {
      margin-bottom: 0;
      border-radius: 0;
    }
    
    /* Set height of the grid so .sidenav can be 100% (adjust as needed) */
    .row.content {height: 100%}
    
    /* Set gray background color and 100% height */
    .sidenav {
      padding-top: 20px;
      background-color: #f1f1f1;
      height: 100%;
    }
    
    /* Set black background color, white text and some padding */
    footer {
      background-color: #555;
      color: white;
      padding: 15px;
    }
    
    /* On small screens, set height to 'auto' for sidenav and grid */
    @media screen and (max-width: 767px) {
      .sidenav {
        height: auto;
        padding: 15px;
      }
      .row.content {height:auto;} 
    }

  </style>
</head>
<body>
	<?php include_once('nav.php');?>

<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-sm-3 sidenav">
	
		<div class="col-sm-12">
			<p>
				<form name="searchXml" action="index.php">
					<input type="hidden" name="xmlDoi" id="xmlDoi" value="<?php echo $_REQUEST['xml']; ?>">
					<input type="text" name="xml" placeholder="Search DOI..">
				</form>
			</p>
			
		<?php
			if (isset($_REQUEST['xml']) && !empty($_REQUEST['xml']) && $_REQUEST['xml'] != "bookin") {
			?>
			
			<br/>
			<p><input class="btn btn-primary btn-block" type="submit" name="updateManuscriptSubmit" id="updateManuscriptSubmit" value="Update Main Manuscript"></p>
			<br/><br/>
			<!--<p><input class="btn btn-primary btn-block" type="button" name="genFigureInfo" id="genFigureInfo" value="Generate Manuscript Approved"></p>-->
			<!-- <p><input class="btn btn-primary btn-block" type="button" name="genFigureInfo" id="genFigureInfo" value="Generate Figure Info"></p>
			<p><input class="btn btn-success btn-block" type="button" name="genPageDetails" id="genPageDetails" value="Generate Page Details"></p>
			-->
			<p>
			<?php if($workflows['UncorrectedManPubOnline'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['UncorrectedManPubOnline'])?> btn-block" type="button" id="action-5" 
					<?=((empty($nexStepCodes) && $workflows['UncorrectedManPubOnline'] != 'no' ) || in_array('UncorrectedManPubOnline', $nexStepCodes)?null:"disabled='disabled'")?>>
					Uncorrected Manuscript Published Online
				</button>
			<?php endif;?>
			<?php if($workflows['ManuscriptToCopyEditor'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ManuscriptToCopyEditor'])?> btn-block" type="button" id="action-7" 
					<?=((empty($nexStepCodes) && $workflows['ManuscriptToCopyEditor'] != 'no' ) || in_array('ManuscriptToCopyEditor', $nexStepCodes))?null:"disabled='disabled'"?>>
					To Copy Editor
				</button>
			<?php endif;?>
			<?php if($workflows['ManuscriptFromCopyEditor'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ManuscriptFromCopyEditor'])?> btn-block" type="button" id="action-8" 
					<?=((empty($nexStepCodes) && $workflows['ManuscriptFromCopyEditor'] != 'no' ) || in_array('ManuscriptFromCopyEditor', $nexStepCodes)?null:"disabled='disabled'")?>>
					Manuscript From Copy Editor
				</button>
			<?php endif;?>
			<?php if($workflows['ProofToProofReader'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ProofToProofReader'])?> proofto btn-block" type="button" name="genProofTo" id="genProofTo" 
					<?=((empty($nexStepCodes) && $workflows['ProofToProofReader'] != 'no' ) || in_array('ProofToProofReader', $nexStepCodes)?null:"disabled='disabled'")?>>
					Generate Proof to Proof Reader
				</button>
			<?php endif;?>
			<?php if($workflows['ProofCorrexFromProofreader'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ProofCorrexFromProofreader'])?> prooffrom btn-block" type="button" name="genProofFrom" id="genProofFrom" 
					<?=((empty($nexStepCodes) && $workflows['ProofCorrexFromProofreader'] != 'no' ) || in_array('ProofCorrexFromProofreader', $nexStepCodes)?null:"disabled='disabled'")?>>
					Generate Proof from Proof Reader
				</button>
			<?php endif;?>
			<?php if($workflows['Proof'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['Proof'])?> btn-block" type="button" id="action-1" 
					<?=((empty($nexStepCodes) && $workflows['Proof'] != 'no' ) || in_array('Proof', $nexStepCodes)?null:"disabled='disabled'")?>>
					Proofs Out
				</button>
			<?php endif;?>
			<?php if($workflows['ProofCorrectionsReceived'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ProofCorrectionsReceived'])?> btn-block" type="button" id="action-2" 
					<?=((empty($nexStepCodes) && $workflows['ProofCorrectionsReceived'] != 'no' ) || in_array('ProofCorrectionsReceived', $nexStepCodes)?null:"disabled='disabled'")?>>
					All Corrections Received
				</button>			
			<?php endif;?>

                    <?php if($workflows['ReviseToEditor'] != 'no'):?>
                        <button class="btn <?=btntheme($workflows['ReviseToEditor'])?> btn-block" type="button" id="action-revisedtoeditor"
                            <?=((empty($nexStepCodes) && $workflows['ReviseToEditor'] != 'no' ) || in_array('ReviseToEditor', $nexStepCodes)?null:"disabled='disabled'")?>>
                            Revise to Editor
                        </button>
                    <?php endif;?>

                    <?php if($workflows['ReviseCorrectionsFromEditor'] != 'no'):?>
                        <button class="btn <?=btntheme($workflows['ReviseCorrectionsFromEditor'])?> btn-block" type="button" id="action-revisedcorfromeditor"
                            <?=((empty($nexStepCodes) && $workflows['ReviseCorrectionsFromEditor'] != 'no' ) || in_array('ReviseCorrectionsFromEditor', $nexStepCodes)?null:"disabled='disabled'")?>>
                            Revise Corrections from Editor
                        </button>
                    <?php endif;?>



			<?php if($workflows['ManuscriptApproved'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ManuscriptApproved'])?> btn-block" type="button" name="genFigureInfo" id="genFigureInfo" 
					<?=((empty($nexStepCodes) && $workflows['ManuscriptApproved'] != 'no' ) || in_array('ManuscriptApproved', $nexStepCodes)?null:"disabled='disabled'")?>>
					Generate Manuscript Approved
				</button>			
			<?php endif;?>
			<?php if($workflows['ManuscriptPubOnline'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ManuscriptPubOnline'])?> btn-block" type="button" id="action-4"
					<?=((empty($nexStepCodes) && $workflows['ManuscriptPubOnline'] != 'no' ) || in_array('ManuscriptPubOnline', $nexStepCodes)?null:"disabled='disabled'")?>>
					Manuscript Published Online
				</button>			
			<?php endif;?>
			<?php if($workflows['ManuscriptPublishedIntoContinuousPublication'] != 'no'):?>
				<button class="btn <?=btntheme($workflows['ManuscriptPublishedIntoContinuousPublication'])?> btn-block" type="button" id="action-6" 
					<?=((empty($nexStepCodes) && $workflows['ManuscriptPublishedIntoContinuousPublication'] != 'no' ) || in_array('ManuscriptPublishedIntoContinuousPublication', $nexStepCodes)?null:"disabled='disabled'")?>>
					Manuscript Published Into Continuous Publication
				</button>
			<?php endif;?>
			</p>
			<!-- <p>
				<div class="dropdown">
				  <button class="btn btn-warning dropdown-toggle  btn-block" type="button" data-toggle="dropdown">Select Step Code..
				  <span class="caret"></span></button>
				  <ul class="dropdown-menu">
					<li><a href="#" id="action-1">Proofs Out</a></li>
					<li><a href="#" id="action-2">All Corrections Received</a></li>
					<li><a href="#" id="action-3">Manuscript Approved for Publication</a></li>
					<li><a href="#" id="action-4">Manuscript Published Online</a></li>
					<li><a href="#" id="action-5">Uncorrected Manuscript Published Online</a></li>
					<li><a href="#" id="action-6">Manuscript Published Into Continuous Publication</a></li>
					<li><a href="#" id="action-7">To Copy Editor</a></li>
					<li><a href="#" id="action-8">Manuscript From Copy Editor</a></li>
					This is <li><a href="#" id="action-12">Issue Pages</a></li>
				  </ul>
				</div>
			</p>
			
			<br/><br/>
			<p><input class="btn proofto btn-block" type="button" name="genProofTo" id="genProofTo" value="Generate Proof to Proof Reader"></p>
			<p><input class="btn prooffrom btn-block" type="button" name="genProofFrom" id="genProofFrom" value="Generate Proof from Proof Reader"></p> 
		
			-->
			
			

			<!--
			<p>
				<div class="dropdown">
				  <button class="btn btn-warning dropdown-toggle  btn-block" type="button" data-toggle="dropdown">Select Issue Code..
				  <span class="caret"></span></button>
				  <ul class="dropdown-menu">
					<li><a href="#" id="action-5">Issue Approved for Publication</a></li>
					<li><a href="#" id="action-6">Issue Order Requested</a></li>
					<li><a href="#" id="action-7">Issue to Printer</a></li>
					<li><a href="#" id="action-8">Issue Correction from Ed Office</a></li>
					<li><a href="#" id="action-9">Issue Paginated</a></li>
					<li><a href="#" id="action-10">Issue Order Received</a></li>
					<li><a href="#" id="action-11">Issue Published Online</a></li>
				  </ul>
				</div>
			</p>
			-->
			
			<?php } 
				
		?>
		
		</div>
		
		
    </div>
	
	
    <div class="col-sm-8 text-left"> 
		<div style="width:650px; margin: 0 auto 0 auto">
		<?php
			if ($upload) {
				?>
				<form action = "" method = "POST" enctype = "multipart/form-data">
					<div class="col-sm-12 mx-auto">
						<input name="filesToUpload" id="filesToUpload" type="file" multiple="" /><input type = "submit"/>
						<br/><br/>
					</div>
				</form>
				<?php
			}
				
		?>
			<?php 
				if (isset($_REQUEST['xml']) && !empty($_REQUEST['xml'])) {
					$addToUrl = $_REQUEST['xml'];
				} elseif (isset($_REQUEST['iss'])) {
					$addToUrl = "&iss=true&issSubmit=true";
				} else {
					$addToUrl = "bookin";
				}
				

			?>
			<form id="realForm" action="?xml=<?php echo $addToUrl; ?>" method="post">
				<div id="dhxForm">
					<?php
						if (is_array($alert)) {
							
							switch($alert[1]) {
								case 1:
									?>
									<div class="alert alert-danger alert-dismissable fade in">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Info: </strong> <?php echo $alert[0]; ?></br>
										<?php
											if (!empty ($alert[4])) {
												?>
													<strong>Info: </strong> <?php echo $alert[5]; ?> <br/>
													<strong>Info: </strong> <?php echo $alert[4]; ?>
												<?php
											}
										
										?>
										
									</div>
									<?php
									break;
								case 2:
									?>
									<div class="alert alert-success alert-dismissable fade in">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Info: </strong> <?php echo $alert[0]; ?></br>
										<?php
											if (!empty ($alert[4])) {
												?>
													<strong>Info: </strong> <?php echo $alert[5]; ?> <br/>
													<strong>Info: </strong> <?php echo $alert[4]; ?>
												<?php
											}
										
										?>
									</div>
									<?php
									break;
								case 3:
									?>
									<div class="alert alert-info alert-dismissable fade in">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Info: </strong> <?php echo $alert[0]; ?></br>
										<?php
											if (!empty ($alert[4])) {
												?>
													<strong>Info: </strong> <?php echo $alert[5]; ?> <br/>
													<strong>Info: </strong> <?php echo $alert[4]; ?>
													
												<?php
											}
										
										?>
									</div>
									<?php
									break;
								case 4:
									?>
									<div class="alert alert-warning alert-dismissable fade in">
										<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
										<strong>Info: </strong> <?php echo $alert[0]; ?></br>
										<?php
											if (!empty ($alert[4])) {
												?>
													<strong>Info: </strong> <?php echo $alert[5]; ?> <br/>
													<strong>Info: </strong> <?php echo $alert[4]; ?>
												<?php
											}
										
										?>
									</div>
									<?php
									break;
								default:
									break;
							}
							
						}
					
					
					?>

					<input type="hidden" name="updateManuscript" id="updateManuscript" value="">
			<?php
				if (isset($_REQUEST['xml']) && !empty($_REQUEST['xml']) && $_REQUEST['xml'] != "bookin") {
				?>
				
				<div class="col-sm-6">
			
					<h4><a href="#" id="addScnt">Add Member</a></h4>
					<div id="p_scents">
						<p>
							<label for="p_scnts">
							</label>
						</p>
					</div>
				</div>
				
				<div class="col-sm-6">
			
					<h4><a href="#" id="addScnt2">Add Figure</a></h4>
					<div id="p_scents2">
						<p>
							<label for="p_scnts2">
							</label>
						</p>
					</div>
				</div>
				<?php } else if (!isset($_REQUEST['iss']) && !isset($_REQUEST['upl'])) {
				?>
					
					<div class="col-sm-6">
			
						<h4><a href="#" id="addScnt3">Add Author</a></h4>
						<div id="p_scents3">
							<p>
								<label for="p_scnts3">
								</label>
							</p>
						</div>
					</div>
					
					
				<?php
				}
			?>

			
				<?php

					if ($_REQUEST['iss'] == true) {
						echo '<p>Completion Date: <input type="text" id="datepicker" name="Completion_Date"></p>';
					} else if ($_REQUEST['xml'] != '') {
						echo '<p>Completion Date: <input type="text" id="datepicker" name="CompletionDate"></p>';
					} else {
						
					}
					
				?>
				
				</div>
				
			</form>
		</div>    
	</div>
	
	

  </div>
</div>


</body>
</html>

<script type="text/javascript">


	$(document).ready(function(){
		doOnLoad();
	});
	
	
	$('#Workflow_Step').val("20").change();

	
	$('input[name="Year"]').click(function () {
		//alert('waaaaaaaa');
		//alert($(this).data('value'));
	});
	
	$(document).ready(function() {    
		$('input[id=datepicker]').datepicker({                 
			 changeMonth: true,
			 changeYear: true,
			 dateFormat: 'yy-mm-dd'                     
		});
	});
	
	function doOnLoad() {
		var myForm;
		myForm = new dhtmlXForm("dhxForm");
		myForm.loadStruct('<?php echo $xmlForm; ?>', function(){
			var steps = <?=json_encode($nexStepCodes)?>;
			if(steps.length > 0){
				steps.push("CoverImageReceived");
				steps.push("IssuePages");
				var combox = myForm.getCombo('Workflow_Step');
				var opts = [];
				for(i=0;i<steps.length; i++){ 
					opts.push([steps[i],steps[i]]);
				}
				if(combox && opts) {
					combox.addOption(opts);
					combox.setComboText(steps[0]);
				}
			}
		});


		myForm.enableLiveValidation(true);
		//remove select items not in list

		myForm.attachEvent("onChange", function (Journal_Code){
			var jj = myForm.getItemValue(Journal_Code);
			switch (jj) {
				case "LOGCOM":
					$('input[name=Journal_Description]').val('Journal of Logic and Computation');
					break;
				case "JIGPAL":
					$('input[name=Journal_Description]').val('Logic Journal of the IGPL');
					break;
				case "SEMANT":
					$('input[name=Journal_Description]').val('Journal of Semantics');
					break;
				case "TEAMAT":
					$('input[name=Journal_Description]').val('Teaching Mathematics and Its Applications: International Journal of the IMA');
					break;
				case "IMAIAI":
					$('input[name=Journal_Description]').val('Information and Inference: a Journal of the IMA');
					break;
				case "IMAMAN":
					$('input[name=Journal_Description]').val('IMA Journal of Management Mathematics');
					break;
				case "IMAMAT":
					$('input[name=Journal_Description]').val('IMA Journal of Applied Mathematics');
					break;
				case "IMAMCI":
					$('input[name=Journal_Description]').val('IMA Journal of Mathematical Control and Information');
					break;
				case "IMAMMB":
					$('input[name=Journal_Description]').val('Mathematical Medicine and Biology: A Journal of the IMA');
					break;
				case "IMANUM":
					$('input[name=Journal_Description]').val('IMA Journal of Numerical Analysis');
					break;
				case "IMATRM":
					$('input[name=Journal_Description]').val('Transactions of Mathematics and Its Applications');
					break;
					
				/*	------------------------------- */
				
				case "BRIBIO":
					$('input[name=Journal_Description]').val('Briefings In Bioinformatics');
					break;
				case "BRIFUN":
					$('input[name=Journal_Description]').val('Briefings in Functional Genomics');
					break;
				case "DATABA":
					$('input[name=Journal_Description]').val('Database');
					break;
				case "HMG..J":
					$('input[name=Journal_Description]').val('Human Molecular Genetics');
					break;
				case "SOCAFN":
					$('input[name=Journal_Description]').val('Social Cognitive And Affective Neuroscience');
					break;
				case "JFREGU":
					$('input[name=Journal_Description]').val('Journal of Financial Regulation');
					break;
				case "HRLREV":
					$('input[name=Journal_Description]').val('Human Rights Law Review');
					break;
				case "JIELAW":
					$('input[name=Journal_Description]').val('Journal Of International Economic Law');
					break;
				case "ANTHER":
					$('input[name=Journal_Description]').val('Antibody Therapeutics ');
					break;
					
				case "TRANSP":
					$('input[name=Journal_Description]').val('Transportation Safety and Environment');
					break;
					
					
					
				/*	------------------------------- */
				case "JMCBIO":
					$('input[name=Journal_Description]').val('Journal of Molecular Cell Biology');
					break;
				case "PLANKT":
					$('input[name=Journal_Description]').val('Journal of Plankton Research');
					break;
				case "CSCH.J":
					$('input[name=Journal_Description]').val('Children and Schools');
					break;
				case "HSWORK":
					$('input[name=Journal_Description]').val('Health and Social Work');
					break;
				case "SWORKJ":
					$('input[name=Journal_Description]').val('Social Work');
					break;
				
				case "SWR..J":
					$('input[name=Journal_Description]').val('Social Work Research');
					break;
				case "CONPHY":
					$('input[name=Journal_Description]').val('Conservation Physiology');
					break;
				case "TREEPH":
					$('input[name=Journal_Description]').val('Tree Physiology');
					break;
				case "ABBSIN":
					$('input[name=Journal_Description]').val('Acta Biochimica et Biophysica Sinica');
					break;
				case "BRIMED":
					$('input[name=Journal_Description]').val('British Medical Bulletin');
					break;
				
				case "CERCOR":
					$('input[name=Journal_Description]').val('Cerebral Cortex');
					break;
				case "JOCLEC":
					$('input[name=Journal_Description]').val('Journal of Competition Law & Economics');
					break;
				case "MOLLUS":
					$('input[name=Journal_Description]').val('Journal of Molluscan Studies');
					break;
				case "HROPEN":
					$('input[name=Journal_Description]').val('Human Reproduction Open');
					break;
				case "HUMREP":
					$('input[name=Journal_Description]').val('Human Reproduction');
					break;
				
				case "HUMUPD":
					$('input[name=Journal_Description]').val('Human Reproduction Update');
					break;
				case "MOLEHR":
					$('input[name=Journal_Description]').val('Molecular Human Reproduction');
					break;
				case "JTMEDI":
					$('input[name=Journal_Description]').val('Journal of Travel Medicine');
					break;
				case "OMCREP":
					$('input[name=Journal_Description]').val('Oxford Medical Case Reports');
					break;
				case "QMATHJ":
					$('input[name=Journal_Description]').val('The Quarterly Journal of Mathematics');
					break;
				
				/*	------------------------------- */
				/*		Tranche 2					*/
				/*	------------------------------- */
				
				case "AJEPID":
					$('input[name=Journal_Description]').val('American Journal of Epidemiology');
					break;
				case "AJEREV":
					$('input[name=Journal_Description]').val('Epidemiologic Reviews');
					break;
				case "ANATOX":
					$('input[name=Journal_Description]').val('Journal of Analytical Toxicology');
					break;
				case "ARCLIN":
					$('input[name=Journal_Description]').val('Archives of Clinical Neuropsychology');
					break;
				case "CCCRIT":
					$('input[name=Journal_Description]').val('Communication, Culture & Critique');
					break;
				
				case "CHRSCI":
					$('input[name=Journal_Description]').val('Journal of Chromatographic Science');
					break;
				case "COMJNL":
					$('input[name=Journal_Description]').val('The Computer Journal');
					break;
				case "COMTHE":
					$('input[name=Journal_Description]').val('Communication Theory');
					break;
				case "DEAFED":
					$('input[name=Journal_Description]').val('Journal of Deaf Studies And Deaf Education');
					break;
				case "EREH.J":
					$('input[name=Journal_Description]').val('European Review of Economic History');
					break;
				
				case "GLYCOB":
					$('input[name=Journal_Description]').val('Glycobiology');
					break;
				case "HOLGEN":
					$('input[name=Journal_Description]').val('Holocaust and Genocide Studies');
					break;
				case "HUMCOM":
					$('input[name=Journal_Description]').val('Human Communication Research');
					break;
				case "IJOLCT":
					$('input[name=Journal_Description]').val('International Journal of Low-Carbon Technologies');
					break;
				case "ILARJO":
					$('input[name=Journal_Description]').val('The ILAR Journal');
					break;
				
				case "IWCOMP":
					$('input[name=Journal_Description]').val('Interacting with Computers');
					break;
				case "JAFECO":
					$('input[name=Journal_Description]').val('Journal of African Economies');
					break;
				case "JCMCOM":
					$('input[name=Journal_Description]').val('Journal of Computer-Mediated Communication');
					break;
				case "JMICRO":
					$('input[name=Journal_Description]').val('Microscopy');
					break;
				case "JNLCOM":
					$('input[name=Journal_Description]').val('Journal of Communication');
					break;
					
					
					
				
				/*	------------------------------- */
				/*		Tranche 3					*/
				/*	------------------------------- */
				
				case "AFRAFJ":
					$('input[name=Journal_Description]').val('African Affairs');
					break;
				case "AGEING":
					$('input[name=Journal_Description]').val('Age And Ageing');
					break;
				case "ALCALC":
					$('input[name=Journal_Description]').val('Alcohol And Alcoholism');
					break;
				case "BIOLRE":
					$('input[name=Journal_Description]').val('Biology of Reproduction');
					break;
				case "CDJ..J":
					$('input[name=Journal_Description]').val('Community Development Journal');
					break;
					
				case "CONPEC":
					$('input[name=Journal_Description]').val('Contributions To Political Economy');
					break;
				case "DOTESO":
					$('input[name=Journal_Description]').val('Diseases of the Esophagus');
					break;
				case "EURRAG":
					$('input[name=Journal_Description]').val('European Review Of Agricultural Economics');
					break;
				case "FORESJ":
					$('input[name=Journal_Description]').val('Forestry: An International Journal Of Forest Research');
					break;
				case "INTBIO":
					$('input[name=Journal_Description]').val('Integrative Biology');
					break;

				case "INTHEA":
					$('input[name=Journal_Description]').val('International Health');
					break;	
				case "INTQHC":
					$('input[name=Journal_Description]').val('International Journal For Quality In Health Care');
					break;	
				case "JECLAP":
					$('input[name=Journal_Description]').val('Journal of European Competition Law and Practice');
					break;
				case "JJCO.J":
					$('input[name=Journal_Description]').val('Japanese Journal Of Clinical Oncology');
					break;
				case "JLBIOS":
					$('input[name=Journal_Description]').val('Journal of Law and the Biosciences');
					break;
					
				case "JSCREP":
					$('input[name=Journal_Description]').val('Journal of Surgical Case Reports');
					break;
				case "MILMED":
					$('input[name=Journal_Description]').val('Military Medicine ');
					break;	
				case "NSRSCP":
					$('input[name=Journal_Description]').val('National Science Review');
					break;
				case "PCMEDI":
					$('input[name=Journal_Description]').val('Precision Clinical Medicine');
					break;
				case "PHYSTH":
					$('input[name=Journal_Description]').val('Physical Therapy');
					break;
					
				case "PROENG":
					$('input[name=Journal_Description]').val('Protein Engineering, Design, and Selection');
					break;
				case "PUBMED":
					$('input[name=Journal_Description]').val('Journal Of Public Health');
					break;
				case "RADDOS":
					$('input[name=Journal_Description]').val('Radiation Protection Dosimetry');
					break;
				case "RADRES":
					$('input[name=Journal_Description]').val('Journal of Radiation Research');
					break;
				case "SOCFOR":
					$('input[name=Journal_Description]').val('Social Forces');
					break;
				case "TRSTMH":
					$('input[name=Journal_Description]').val('Transactions of the Royal Society of Tropical Medicine and Hygiene');
					break;
					
				/*	------------------------------- */
				/*	------------------------------- */
				case "OXECON":
					$('input[name=Journal_Description]').val('Oxford Open Economics');
					break;
				case "OXFNRG":
					$('input[name=Journal_Description]').val('Oxford Open Energy');
					break;
				case "JOSPRM":
					$('input[name=Journal_Description]').val('Journal of Surgical Protocols and Research Methodologies');
					break;
				case "JJBIOC":
					$('input[name=Journal_Description]').val('The Journal of Biochemistry');
					break;
				case "PETROJ":
					$('input[name=Journal_Description]').val('Journal of Petrology');
					break;
			}
		});

		myForm.attachEvent("onButtonClick", function(name) {
			x = myForm.validate();
			if(x) {
                document.getElementById("realForm").submit();
			} else {
				alert("Form Incomplete");
			}
		});
		
	}
		
	
	
	
	
	$('#searchXml').submit(function() {
		str = $('#searchText').val();
		if (str) {
			var s = 'index.php?xml='+str;
			alert(s);
			window.location.href = 'index.php?xml='+str;
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	$('#genPageDetails').click(function() {
		str = $('#xmlDoi').val();
		str2 = $('#searchText').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&doi='+str;
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	$('#genWorkflowStep').click(function() {
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str;
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	
	$('#genProofTo').click(function() {
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&proofTo='+str+'&stepCode=ProofToProofReader';
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	$('#genProofFrom').click(function() {
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&proofFrom='+str+'&stepCode=ProofCorrexFromProofreader';
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	
	
	$('#updateManuscriptSubmit').click(function() {
		str = $('#updateManuscriptSubmit').attr('id');
		$('#updateManuscript').val(str);
		$('form#realForm').submit();
	});
	
	$('#genFigureInfo').click(function() {
		str = $('#xmlDoi').val();
		str2 = $('#searchText').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&fig='+str+'&stepCode=ManuscriptApproved';
			return false;
		} else {
			alert("DOI is required");
		}
		
	});
	
	$('#bookIn').click(function() {
		window.location.href = 'index.php';
		return false;
	});
	
	
	
	
	$(function() {
        var scntDiv = $('#p_scents');
		<?php 
			if (isset($form[1]) && !empty($form[1])) {
				?>
					var count = <?php echo $form[1]; ?>;
				<?php
			} else {
				?>
					var count = false;
				<?php
			}
		
		?>
		if(count){
			var i = count+1;
			
			$('#addScnt').click(function() {
					$('<p><label for="p_scnts"><input type="text" id="p_scnt" size="20" name="p_scnt_memRole_' + i +'" value="" placeholder="Input Role" /><input type="text" id="p_scnt" size="20" name="p_scnt_memTitle_' + i +'" value="" placeholder="Input Title" /><input type="text" id="p_scnt" size="20" name="p_scnt_memFirstName_' + i +'" value="" placeholder="Input First Name" /><input type="text" id="p_scnt" size="20" name="p_scnt_memLastName_' + i +'" value="" placeholder="Input Last Name" /><input type="text" id="p_scnt" size="20" name="p_scnt_memEmail_' + i +'" value="" placeholder="Input Email" /></label>                           </p>').appendTo(scntDiv);
					i++;
					return false;
			});
			
			$('#remScnt').click(function() { 
					if( i > 2 ) {
							$(this).parents('p').remove();
							i--;
					}
					return false;
			});
		}
		
		
		
		var scntDiv2 = $('#p_scents2');
		<?php 
			if (isset($form[2]) && !empty($form[2])) {
				?>
					var count = <?php echo $form[2]; ?>;
				<?php
			} else {
				?>
					var count = 0;
				<?php
			}
		
		?>
		var i = count+1;
		
		$('#addScnt2').click(function() {
				$('<p><label for="p_scnts2"><input type="text" id="p_scnt2" size="20" name="p_scnt_figColourPrint_' + i +'" value="" placeholder="Input Colour Print" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figColourOnline_' + i +'" value="" placeholder="Input Colour Online" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figChargeWaived_' + i +'" value="" placeholder="Input Charge Waived" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figChargeConfirmed_' + i +'" value="" placeholder="Input Charge Confirmed" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figPage_' + i +'" value="" placeholder="Input Page" /></label>                          <input type="text" id="p_scnt2" size="20" name="p_scnt_figPassword_' + i +'" value="" placeholder="Input Admin Password" /> </p>').appendTo(scntDiv2);
				i++;
				return false;
		});
		
		$('#remScnt2').click(function() { 
				if( i > 2 ) {
						$(this).parents('p').remove();
						i--;
				}
				return false;
				
		});
		
		
		
		var scntDiv3 = $('#p_scents3');
		<?php 
			if (isset($form[2]) && !empty($form[2])) {
				?>
					var count = <?php echo $form[2]; ?>;
				<?php
			} else {
				?>
					var count = 0;
				<?php
			}
		
		?>
		var i = count+1;
		
		$('#addScnt3').click(function() {
				$('<p><label for="p_scnts2"><input type="text" id="p_scnt2" size="6" name="p_scnt_figCorr_' + i +'" value="" placeholder="Corresponding? (True or False)" /><br/><input type="text" id="p_scnt2" size="6" name="p_scnt_figLead_' + i +'" value="" placeholder="Lead? (True or False)" /><br/><input type="text" id="p_scnt2" size="6" name="p_scnt_figRole_' + i +'" value="" placeholder="Role" /><br/><br/>                <input type="text" id="p_scnt2" size="20" name="p_scnt_figForeName_' + i +'" value="" placeholder="Input ForeName" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figMiddleName_' + i +'" value="" placeholder="Input Middle Name" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figSurname_' + i +'" value="" placeholder="Input Surname" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figTitle_' + i +'" value="" placeholder="Input Title" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figEmail_' + i +'" value="" placeholder="Input Email" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figInstitution_' + i +'" value="" placeholder="Input Institution" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figDepartment_' + i +'" value="" placeholder="Input Department" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figAddress1_' + i +'" value="" placeholder="Input Address1" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figAddress2_' + i +'" value="" placeholder="Input Address2" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figTown_' + i +'" value="" placeholder="Input Town" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figCountry_' + i +'" value="" placeholder="Input Country" /><input type="text" id="p_scnt2" size="20" name="p_scnt_figPostcode_' + i +'" value="" placeholder="Input Post Code" /></label>                           </p><br/>').appendTo(scntDiv3);
				i++;
				return false;
		});
		
		$('#remScnt3').click(function() { 
				if( i > 2 ) {
						$(this).parents('p').remove();
						i--;
				}
				return false;
				
		});
		
		
});
	jQuery("#action-1").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=Proof&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-2").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ProofCorrectionsReceived&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-3").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ManuscriptApproved&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	
	jQuery("#action-4").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ManuscriptPubOnline&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	
	/*	Added for Issue
	*/
	
	jQuery("#action-5").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=UncorrectedManPubOnline&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	
	/*	Added for ManuscriptPublishedIntoContinuousPublication
		~jam
	*/
	jQuery("#action-6").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ManuscriptPublishedIntoContinuousPublication&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	
	
	
	jQuery("#action-60").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&iss='+str+'&stepCode=IssueOrderReceived&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-7").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ManuscriptToCopyEditor&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-8").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=ManuscriptFromCopyEditor&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-9").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&iss='+str+'&stepCode=IssueApprovedForPublication&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-10").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&iss='+str+'&stepCode=IssueToPrinter&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	jQuery("#action-11").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&iss='+str+'&stepCode=IssuePublishedOnline&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});
	
	jQuery("#action-12").click(function(e){
		date = $("input[name=CompletionDate]").val();
		str = $('#xmlDoi').val();
		str2 = $('#xml').val();
		if (str) {
			window.location.href = 'index.php?xml='+str+'&wfs='+str+'&stepCode=IssuePages&date='+date;
			return false;
		} else {
			alert("DOI is required");
		}
		e.preventDefault();
	});



	
</script>