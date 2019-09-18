<?php/** * Created by PhpStorm. * User: a1538_000 * Date: 2019/9/14 * Time: 16:28 */namespace Main\Controller;use think\Controller;use Think\Exception;class TablesController extends Controller{    public function Main(){        $this->display("index");    }    public function getData(){        $models = D("Tables");        $data = $models->where(["company_id"=>getUserId()])->field('id,file_name,status,type,create_time,qrcode')->select();        foreach ($data as $key => &$value){            if ($data[$key]['status'] == 0){                $data[$key]['status'] = "开启";            }else{                $data[$key]['status'] = "关闭";            }            if ($data[$key]['type'] == 1){                $data[$key]['type'] = "表格";            }else{                $data[$key]['type'] = "图片";            }            if($data[$key]['create_time']){                $data[$key]['create_time'] = date("Y-m-d H:i:s",$data[$key]['create_time']);            }            if (empty($data[$key]['qrcode']) || $data[$key]['qrcode'] == NULL){                // 创建并返回地址                 $qr_local_url = $this->create_qrcode("http://".$_SERVER['SERVER_NAME']."/Show/getQrcode?id=".$data[$key]['id']."&code=".uniqid());                // 保存到数据库                $saveData['qrcode'] = "/Qrcode/".$qr_local_url;                $result = $models->where(['id'=>$data[$key]['id']])->field("qrcode")->save($saveData);                if ($result){                    $data[$key]['qrcode'] = $saveData['qrcode'];                }            }        }//        dump($data);        $this->ajaxReturn(onOk($data));    }    // 生成二维码    public function create_qrcode($url){        header('Content-Type: image/png');        // 判断URL是否合法        // Todo...        $size=5;        Vendor('phpqrcode.phpqrcode');        $errorCorrectionLevel ="L" ;//容错级别        $matrixPointSize = intval($size);//生成图片大小        //生成二维码图片        try{            $object = new \QRcode();            $filpath = "./Public/Qrcode/";            $filename = md5(rand()).".png";            // "../Public/Qrcode/".$url.".png"            ob_clean();            $object->png($url,$filpath.$filename,$errorCorrectionLevel, $matrixPointSize,1);            return $filename;        }catch (Exception $e){            return $e->getMessage();        }    }    public function add_table(){        if (!IS_POST){            $this->display("add_table");        }else{            $inputData = I("post.");            $inputData['type'] = 1;            if ($_FILES){                $inputData['table_head'] = $this->handleImg($_FILES['file']);            }            $inputData['company_id'] = getUserId();            $inputData['create_time'] = time();            $inputData['id'] = getID();            $tableModel = D("Tables");            $result = $tableModel->add($inputData);            if ($result){                $this->ajaxReturn(onOk("添加成功"));            }else{                $this->ajaxReturn(onError("未知错误"));            }        }    }    // 添加图片    public function add_pic(){        if (!IS_POST){            $this->display("add_pic");        }else{            $inputData = I("post.");            $inputData['type'] = 2;            if ($_FILES){                $inputData['table_content'] = $this->handleImg($_FILES['file']);            }            $inputData['company_id'] = getUserId();            $inputData['create_time'] = time();            $inputData['id'] = getID();            $tableModel = D("Tables");            $result = $tableModel->add($inputData);            if ($result){                $this->ajaxReturn(onOk("添加成功"));            }else{                $this->ajaxReturn(onError("未知错误"));            }        }    }    public function handleImg(){        $upload = new \Think\Upload();// 实例化上传类        $imgSize = 3145728;        $imgType = array('jpg','gif','png','jpeg');        $upload->maxSize   =     $imgSize;// 设置附件上传大小        $upload->exts      =     $imgType;// 设置附件上传类型        $upload->rootPath  =     "./Public/Picture/"; // 设置附件上传根目录        $upload->savePath  =     ''; // 设置附件上传（子）目录        $data = array();        // 上传文件        $info = $upload->upload();        if(!$info){            $data['state'] = 0;            $data['message'] ='上传失败';        };        $path = "../Public/Picture/".$info['file']['savepath'].$info['file']['savename'];        $data['path'] = $path;        return $data['path'];    }}