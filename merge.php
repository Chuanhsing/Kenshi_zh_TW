<?php

$msg = new Msg;
$pos = [
    "po/gamedata.po" => "Kenshi/__translations/zh_TW/gamedata.pot",
    "po/LC_MESSAGES/main.po" => "Kenshi/locale/en_GB/LC_MESSAGES/main.pot",
];
foreach($pos as $po => $pot) {
    $msg->merge($po, $pot);
}

$pod = [
    'po/dialogue/' => 'Kenshi/__translations/zh_TW/dialogue/',
];
foreach($pod as $po_dialogue => $pot_dialogue) {
    $res = $msg->pod($po_dialogue, $pot_dialogue);
    print_r($res);
}

class Msg
{

    function pod($po_dialogue, $pot_dialogue)
    {
        $po_dialogues = array_diff(scandir($po_dialogue), array('..', '.'));
        foreach($po_dialogues as $file) {
            $po_file = $po_dialogue.$file;
            $pot_file = $pot_dialogue.$file.'t';
            if (file_exists($pot_file)) {
                //$cmd = "msgmerge -U \"{$po_file}\" \"{$pot_file}\"";
                //exec($cmd, $output, $return_var);
                $return_var = $this->merge($po_file, $pot_file);
                if ($return_var == 0) {
                    $res['merged'][] = $file;
                } else {
                    $res['failed'][] = $file;
                }
                //echo $cmd."\n"; exit();
            } else {
                $res['missed'][] = $file;
                unlink($po_file);
            }
        }
        return $res;
    }

    function pot_fix($file)
    {
        if (!file_exists($file)) {
            echo "Error not exists $file\n";
            exit();
        }
        $lines = file($file);
        $outs = "";
        foreach($lines as $line) {
            $line = trim($line);
            if (preg_match('#^msgid "(.*)"$#', $line, $pregs)) {
                if ($pregs[1]) {
                    $line = 'msgid "'.str_replace('"', '\\"', $pregs[1]).'"';
                }
            } elseif (preg_match('#^"(.*)"$#', $line, $pregs)) {
                if ($pregs[1]) {
                    $line = '"'.str_replace('"', '\\"', $pregs[1]).'"';
                }
            }
            $outs .= $line."\n";
        }
        file_put_contents("tmp.pot", $outs);
        return "tmp.pot";
    }

    function merge($def, $ref)
    {
        if (!file_exists($def)) {
            echo "Error not exists $def\n";
            exit();
        }
        if (!file_exists($ref)) {
            echo "Error not exists $ref\n";
            exit();
        }
        $tmp = $this->pot_fix($ref);
        $cmd = "msgmerge -U \"$def\" \"$tmp\"";
        exec($cmd, $output, $return_var);
        unlink($tmp);
        return $return_var;
    }

}