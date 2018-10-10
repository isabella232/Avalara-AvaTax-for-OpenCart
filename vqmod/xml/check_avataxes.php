<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tushar.sonje
 * Date: 2/12/14
 * Time: 12:12 PM
 * To change this template use File | Settings | File Templates.
 */

//header('Content-Type: application/pdf');
class check_vqmod_xml{
    private $path;
    private $xml_file_names;
    private $oc_files_to_modify;
    public $is_conflict;
    public $oc_search_string;
    public function __construct(){
        $this->path = dirname(__FILE__);
        $this->xml_file_names = glob($this->path.'/*.xml');
        $this->oc_files_to_modify = array();
        $this->oc_search_string = array();
        $this->is_conflict = false;
        $a = session_id();
        if(!empty($a)){
            session_destroy();
        }
        else{
            session_start();
        }
    }

    public function retrieve_nodes(){
        $file_nodes = array();
        $filenames = $this->xml_file_names;
        if($filenames==null){
            echo "<img src='../". basename(dirname(__FILE__))."/Avalara/images/error_img.png' /><span id='err'>Error : No XML files present in current directory</span>";
            exit();
        }
        if(!in_array($this->path."/avataxes.xml",$filenames)){
            echo "<img src='../". basename(dirname(__FILE__))."/Avalara/images/error_img.png' /><span id='err'>Error : avataxes.xml not present in current directory</span>";
            exit();
        }
        foreach($filenames as $xml){        //Reads all xml files from vqmod
            $file_node = array();
            $xmldoc = new DOMDocument();
            $xmldoc->load($xml);
            $file_name_node = $xmldoc -> getElementsByTagName('file');

            foreach($file_name_node as $file_name_node){        //Reads all file nodes from xml file

                $search_name_node = $file_name_node -> getElementsByTagName('search');
                $no_of_search_nodes = 0 ;
                $search_node = array();
                foreach($search_name_node as $search_name_node){        //Reads all inner search nodes from each file node
                    $search_node[$no_of_search_nodes]= trim($search_name_node->nodeValue,"\n\t ");
                    $no_of_search_nodes+=1;
                }
                if(array_key_exists($file_name_node->getAttribute('name'),$file_node)){
                    foreach($file_node[$file_name_node->getAttribute('name')] as $xyz){
                        array_push($search_node,$xyz);
                    }
                }
                    $file_node[$file_name_node->getAttribute('name')] = $search_node;

            }
            $file_nodes[$xml] = $file_node;
        }
        $this->oc_files_to_modify = $file_nodes;
    }

    public function compare_file_paths(){
        //  $html = "<html><body><table border=\"1\">";

        echo "<table border='1' id='left-wraper'><tr align='center'><td>File Name</td><td>Conflict path name</td><td>Conflicting strings</td>";
        $html = "<html><body><table border=\"1\" cellpadding=\"2\" cellspacing=\"2\">";
        $html = $html."<tr align=\"center\"><td>File Name</td><td>Conflict path name</td><td>Conflicting strings</td></tr>";
        foreach($this->xml_file_names as $file ){
            //    $fp = fopen('Ava_conflict.doc', 'a+');
            $conflict_array = array();
            foreach(array_keys($this->oc_files_to_modify[$this->path.'/avataxes.xml']) as $nodes){
                if (array_key_exists($nodes, $this->oc_files_to_modify[$file]) && $file != $this->path.'/avataxes.xml') {
                    $conflict_values = array();
                    $count_values = 0;
                    $html = $html."<tr><td><b>".$file."</b></td>";
                    $html = $html."<td>".$nodes."</td><td>";
                    echo "<tr><td><b>".$file."</b></td>";
                    echo "<td>".$nodes."</td><td><font color='red'>";
                    $this->is_conflict = true;

                    foreach($this->oc_files_to_modify[$file][$nodes] as $values){
                        if(in_array($values,$this->oc_files_to_modify[$this->path.'/avataxes.xml'][$nodes])){
                            $conflict_values[$count_values] = $values ;
                            $count_values += 1;
                            $html = $html."&nbsp;";
                            $html = $html.htmlentities($values)."<br><br>";
                            echo htmlentities($values)."<br><br>";

                        }else{
                            $html = $html."&nbsp;";
                        }
                    }

                    echo "</font></td></tr>";
                    $html = $html."</td></tr>";
                    $conflict_array[$nodes] = $conflict_values;
                }
            }
            $this->oc_search_string[$file] = $conflict_array;
        }
        $d = new DateTime();

        $html = $html."<tr><td colspan=\"3\" align=\"right\">";
        $html = $html.gmdate("D, d-M-Y H:i:s \G\M\T");
        $html = $html."</td></tr></table></body></html>";
        if(!$this->is_conflict){
            echo "<tr align='center'><td colspan='3' style='font-weight: bold; color: #008000;'>avataxes.xml is not conflicting with other extensions</td></tr>";
            $_SESSION['html'] = "";
        }
        else{
            $_SESSION['html'] = $html;
        }

        echo "</table>";
    }

}