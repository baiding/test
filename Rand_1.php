<?php

$g_arr_day_num = array();	//�������к���
$g_arr_current_num_info = array();	//��ǰ������Ϣ
//$g_arr_history_info = array(); //��ʷ�Դ���Ϣ
$g_i_read_line_count = 0;	//����ȡ�˶�����
$g_arr_current_rands = array(); //��ǰ������б�
$g_arr_chance = array(0,0); //����

$g_file_w = fopen("o.txt", "w") or exit("Unable to open file!");
$g_file_ww = fopen("p.txt", "w") or exit("Unable to open file!");
$g_file = fopen("tx.txt", "r") or exit("Unable to open file!");

Main();

/** 
 * Main
 * desc ������
 * date 2014/10/19 12:22:01
 */
function Main()
{
    global $g_file_w, $g_file_ww, $g_file, $g_arr_day_num,$g_arr_chance;

    $m_str_line = null;		//���ļ���ȡ����
    $m_b_first_num = false;	//��һ�������־
    $m_i_end_num = 0;		//β��
    $m_i_next_num = -32767;
    $m_i_count = 0;

    //Output a line of the file until the end is reached
    while (!feof($g_file)) {

        //CheckExit();
        $m_str_line = fgets($g_file);
        
        if (trim($m_str_line) == "") {
            continue;
        }			//����ǿ����򲻲������

        $m_b_first_num = IsDayFirst($m_str_line);
        if ($m_b_first_num) {
            if (count($g_arr_day_num) > 0) {
                fwrite($g_file_ww,"\t".sprintf("%.2f",($g_arr_chance[0]/($g_arr_chance[0]+$g_arr_chance[1])*100))."\n");
            }
            ClearTempDate();
            $m_i_next_num = -32767;
        }
        $m_i_count = count($g_arr_day_num);
        $m_i_end_num = GetEndNum();
        SetDayNumList($m_i_end_num);
        if($m_i_count > 60){
            if($m_i_count % 10 == 1){
            //if($m_i_count == 61){
                CalcMaxSuccessChance();
                if($m_i_count == 61){
                    PrintHistory();
                }
            }
            if($m_i_next_num != -32767){
                PrintYesNo($m_i_next_num,$m_i_end_num);
            }
            $m_i_next_num = GetNextNum();
        }

    }

    fclose($g_file);
    fclose($g_file_w);
    fclose($g_file_ww);

}


/** 
 * PrintYesNo
 * param $i_next_num 
 * param $i_end_num
 * param $i_index
 * desc ��ӡ��ȷ��ʧ��
 * date 2014/10/21 11:53:55
 */ 
function PrintYesNo($i_next_num,$i_end_num){
    global $g_file_ww,
            $g_arr_chance;
    //if($m_i_next_num/5)
    $m_str_flag = null;
    if (($i_next_num > 0 && floor($i_end_num/5) > 0 ||
         $i_next_num < 0 && floor($i_end_num/5) == 0)) {
        //$m_str_flag = ("I ".$i_end_num." ".$i_next_num."\n");
        $m_str_flag = "I";
        $g_arr_chance[0]++;
        
    }else{
        //$m_str_flag = ("O ".$i_end_num." ".$i_next_num."\n");
        $m_str_flag = "O";
        $g_arr_chance[1]++;
    }
    //$m_str_flag = $i_end_num;
    fwrite($g_file_ww,$m_str_flag.",");
}


/** 
 * PrintHistory
 * desc ��ӡ��ʷδ�������ȷ�����
 * date 2014/10/21 12:47:06
 */ 
function PrintHistory(){
    global $g_arr_day_num,
            $g_file_ww,
            $g_arr_current_num_info;
    fwrite($g_file_ww,$g_arr_current_num_info[0]."\t");

    for ($i = 2; $i < (count($g_arr_day_num) - 1); $i++) {
        $_i_next_num = GetNextNum($i);
        PrintYesNo($_i_next_num,$g_arr_day_num[$i+1]);
    }
    
    //print_r($g_arr_day_num);
}


/** 
 * GetNextNum
 * param $i_index
 * desc �õ���һ������
 * date 2014/10/21 11:20:21
 */ 
function GetNextNum($i_index = -1){
    global $g_arr_current_rands,
            $g_arr_day_num;

    $m_i_count = 0; //��С�ݼ���

    if($i_index == -1)
        $m_i_last_index = (count($g_arr_day_num) -1); //�õ���������±�
    else
        $m_i_last_index = $i_index; //�õ���������±�
    
    $m_arr_seed = array($g_arr_day_num[$m_i_last_index - 2],
                        $g_arr_day_num[$m_i_last_index - 1],
                        $g_arr_day_num[$m_i_last_index]);
    
    foreach ($g_arr_current_rands as $value) {
        $_i_rtn = GetRandNum($m_arr_seed,$value);
        if($_i_rtn >= 5){
            $m_i_count += 1;
        }else{
            $m_i_count -= 1;
        }
    }

    return $m_i_count;
    
}

/** 
 * CheckExit
 * desc ����Ƿ������˳�
 * date 2014/10/19 12:36:45
 */
function CheckExit()
{
    global $g_i_read_line_count;

    if ($g_i_read_line_count > 100) {
        exit();
    }

    $g_i_read_line_count++;
}

/** 
 * IsDayFirst
 * param $str_line 
 * desc �ж��Ƿ���һ���е�һ������
 * date 2014/10/19 10:41:38
 */
function IsDayFirst($str_line)
{
    global $g_arr_current_num_info;

    SplitString($str_line);

    if ($g_arr_current_num_info[1] == "001") {
        return true;
    }
    return false;
}

/** 
 * SplitString
 * param $str_line
 * desc �ָ��ַ���������
 * date 2014/10/19 10:42:58
 */
function SplitString($str_line)
{
    global $g_arr_current_num_info;

    $m_arr_split_rtn1 = split("\t", $str_line);
    $m_arr_split_rtn2 = split("-", $m_arr_split_rtn1[0]);
    $m_arr_split_rtn3 = split(",", $m_arr_split_rtn1[1]);
    $m_arr_result_rtn =
            array(trim($m_arr_split_rtn2[0]), trim($m_arr_split_rtn2[1]),
                  $m_arr_split_rtn3);
    $g_arr_current_num_info = $m_arr_result_rtn;
}

/** 
 * GetEndNum
 * desc �õ����һ������
 * date 2014/10/19 12:02:23
 */
function GetEndNum()
{
    global $g_arr_current_num_info;

    return trim($g_arr_current_num_info[2][4]);
}

/** 
 * SetDayNumList
 * param $num 
 * desc ����ǰ������� $day_num_list ��
 * date 2014/10/19 12:06:54
 */
function SetDayNumList($end_num)
{
    global $g_arr_day_num;

    $g_arr_day_num[] = $end_num;
}

/** 
 * ClearTempDate
 * desc �����ʱ����
 * date 2014/10/19 13:17:40
 */
function ClearTempDate()
{
    global $g_arr_day_num,$g_arr_current_rands,$g_arr_chance;

    $g_arr_day_num = array();
    $g_arr_current_rands = array(); //��ǰ������б�
    $g_arr_chance = array(0,0); //��ǰ������б�
}


/** 
 * GetRandNum
 * param $arr_num 
 * desc ��������������Ϊ���ӵõ������
 * date 2014/10/19 12:09:41
 */
function GetRandNum($arr_num, $i_offset)
{
    $m_i_len = count($arr_num);	//$arr_num ����
    $m_i_seed = 0;		//����ǰ���������
    $m_i_rand = 0;

    for ($i = 0; $i < $m_i_len; $i++) {
        $m_i_seed += ($arr_num[$i] * pow(10, ($m_i_len - $i)) / 10);
    }
    $m_i_seed += $i_offset;	//$m_i_seed * $i_offset1 + $i_offset2;

    srand($m_i_seed);
    $m_i_rand = rand(0, 9);
    //echo $m_i_rand."\n";

    return $m_i_rand;

}

/** 
 * CalcMaxSuccessChance
 * desc ����������ȷ���������
 * date 2014/10/19 12:22:56
 */
function CalcMaxSuccessChance()
{

    global $g_arr_current_rands;

    $m_i_offset = 0;		//ƫ��ֵ
    $m_i_offset_max = 5000000;
    $m_arr_result = array();	//���������߸�����

    $m_i_count = 0;		//��С�ݼ���

    while ($m_i_offset < $m_i_offset_max) {
        /* for ($i = 0; $i < $m_i_offset_max; $i++) { */
        $_arr_result = LoopBody($m_i_offset);

        if ($_arr_result[0] != -1 ) {
            $m_arr_result[] = $_arr_result;
            if (count($m_arr_result) >= 5) {
                break;
            }
        }
        /* } */
        $m_i_offset++;
    }

    $g_arr_current_rands = $m_arr_result;
    

    /* NewNumbersPrintf($m_arr_result);	// :TEST: ��ʱʹ�� */

    /* for ($i = 0; $i < count($m_arr_result); $i++) { */
	/* if ($m_arr_result[$i][7] > 5) { */
	/*     $m_i_count += 1; */
	/* } else { */
	/*     $m_i_count -= 1; */
	/* } */
    /* } */

    /* fwrite($g_file_ww, $m_i_count."\n"); */
}


/** 
 * CheckContinuousNo
 * param $arr_result 
 * desc ����Ƿ�����������
 * date 2014/10/21 17:40:56
 */ 
function CheckContinuousNo($arr_result){

    $m_i_count = count($arr_result);
    $m_i_no_count = 0;
    /* $m_b_flag = false; */
    for ($i = 0; $i < ($m_i_count/2); $i++) {
        if($arr_result[$i] == "O"){
            $m_i_no_count ++;
        }else{
            $m_i_no_count = 0;
        }
        if($m_i_no_count >= (4+(($m_i_count/10-6)/2))){
            /* if(!$m_b_flag){ */
            /*     $m_i_no_count = 0; */
            /*     $m_b_flag = true; */
            /*     continue; */
            /* } */
            return true;
        }
    }
    return false;
}


/** 
 * NewNumbersPrintf
 * param $arr_result 
 * desc �����м�����ĺ�������һ���
 * date 2014/10/21 10:46:05
 */
function NewNumbersPrintf($arr_result)
{
    // :TEST: �Ժ�����ò�����ֻ�ò�����ʹ��
    global $g_file_ww;
    //print_r($arr_result);
    for ($i = 0; $i < count($arr_result[0][1]); $i++) {
        $_i_count = 0;		//��С������
        foreach($arr_result as $value) {
            if ($value[1][$i] == "I") {
                $_i_count += 1;
            } else {
                $_i_count -= 1;
            }
        }
        if ($_i_count > 0) {
            fwrite($g_file_ww, "I,");
        } else {
            fwrite($g_file_ww, "O,");
        }
    }
}


/** 
 * LoopBody
 * param $i_offset1 
 * param $i_offset2 
 * desc text
 * date 2014/10/20 15:45:23
 */
function LoopBody($i_offset)
{
    global $g_arr_day_num;

    $m_i_day_count = count($g_arr_day_num);
    $m_arr_chance_info = array();	//��ǰ������Ϣ����ȷʧ���б�
    $m_f_chance = 0.0;

    for ($i = 2; $i < ($m_i_day_count -1); $i++) {

        $_arr_seed = array($g_arr_day_num[$i-2],
                           $g_arr_day_num[$i - 1],
                           $g_arr_day_num[$i]);

        $_i_next = GetRandNum($_arr_seed, $i_offset);

        /* if ($i == $m_i_day_count - 1) { */
        /*     $m_arr_chance_info[7] = $_i_next; */
        /*     continue; */
        /* } */

        if (floor($_i_next / 5) == floor($g_arr_day_num[$i + 1] / 5)) {
            $m_arr_chance_info[1][] = "I";	//���ʱ��־
            if (array_key_exists(2, $m_arr_chance_info)) {
                $m_arr_chance_info[2]++;	//��ȷ���������
            } else {
                $m_arr_chance_info[2] = 1;	//��ȷ���������
            }

            if ($i <= (($m_i_day_count - 3) / 2)) {
                if (array_key_exists(3, $m_arr_chance_info)) {
                    $m_arr_chance_info[3]++;
                } else {
                    $m_arr_chance_info[3] = 1;
                    $m_arr_chance_info[4] =
                            floor(($m_i_day_count - 3) / 2);
                }
            }

        } else {
            $m_arr_chance_info[1][] = "O";	//���ʱ�ı�־
        }
    }

    /* $_flag = CheckContinuousNo($m_arr_chance_info[1]); */
    /* if(!$_flag){ */
    /*     return array(0 => -1); */
    /* } */
    

    $m_arr_chance_info[5] = sprintf("%.3f",	$m_arr_chance_info[2] / count($m_arr_chance_info[1]) * 100);
    $m_arr_chance_info[0] = $i_offset;
    $m_arr_chance_info[6] = sprintf("%.3f", $m_arr_chance_info[3] / $m_arr_chance_info[4] * 100);
    
    if ($i_offset % 1000000 == 0) {
        echo $i_offset."\n";
    }


    $_chance_offset = 0;
    if($m_i_day_count > 60){
        $_chance_offset = ($m_i_day_count/10-6);
    }
    
    if (($m_arr_chance_info[5] - $m_arr_chance_info[6] > (9+($_chance_offset*1.4))) && $m_arr_chance_info[6] > (55-$_chance_offset*2.7) && $m_arr_chance_info[5] < (70 - $_chance_offset) && $m_arr_chance_info[5] > (67 - ($_chance_offset*2.7))) {
        WriteChanceToFile($m_arr_chance_info);
        //print_r($m_arr_chance_info);
        echo "1 ".$_chance_offset." ";
        return $m_arr_chance_info[0];
    }

    $m_arr_chance_info = array();
    return array(0 => -1);
}


/** 
 * WriteFile
 * param $arr_chance_info 
 * desc ���������Ϣ
 * date 2014/10/19 14:43:40
 */
function WriteChanceToFile($arr_chance_info, $flag = false)
{
    global $g_file_w, $g_file_ww;

    $m_i_len = count($arr_chance_info[1]);	//����
    $m_str_write = null;	//����ַ���

    for ($i = 0; $i < $m_i_len; $i++) {
        $m_str_write .= ($arr_chance_info[1][$i].",");
    }

    if (!$flag) {
        fwrite($g_file_w,
               $arr_chance_info[0]."\t".$arr_chance_info[6]."\t".$arr_chance_info[5]."\t".
               $m_str_write."\n");
    } else {
        fwrite($g_file_ww,
               $arr_chance_info[0]."\t".$arr_chance_info[6]."\t".$arr_chance_info[5]."\t".
               $m_str_write."\n");
    }

}

?>
