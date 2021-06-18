<?php

namespace kuro\lib\excel;

use kuro\exception\LogicException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class Excel
{
    /**
     * 基础列
     *
     * @var array
     */
    private $baseColumn = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    ];

    /**
     * 获取excel列
     *
     * @param integer $column 总列数
     * @return array
     *
     * @throws LogicException
     * @author sunanzhi <sunanzhi@kurogame.com>
     */
    public function getColumn(int $column): array
    {
        if ($column <= 26) {
            return $this->baseColumn;
        } else {
            $floor = floor($column / 26);
            if ($floor > 26) {
                throw new LogicException('列过大,暂不支持');
            }
            $res = $this->baseColumn;
            for ($i = 0; $i < $floor; $i++) {
                $char = $this->baseColumn[$i];
                $tempColumn = [];
                foreach ($this->baseColumn as $mergeChar) {
                    array_push($tempColumn, $char . $mergeChar);
                }
                $res = array_merge($res, $tempColumn);
            }

            return $res;
        }
    }

    /**
     * 导出excel,并返回路径
     *
     * @param array $header
     * @param array $data
     * @param array $config
     * @return IWriter
     *
     * @throws LogicException
     * @throws Exception
     * @author sunanzhi <sunanzhi@kurogame.com>
     */
    public function createTable(array $header, array $data, array $config = []): IWriter
    {
        $sheetTitle = $config['sheetTitle'] ?? '导出';
        $fontSize = 12;
        $spreadsheet = new Spreadsheet();
        # 获取活动工作薄
        $sheet = $spreadsheet->getActiveSheet();
        //居中
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $column = count($header);
        // 设置基本属性
        $sheet->setTitle($sheetTitle);
        $sheet->getStyle($column)->applyFromArray($styleArray)
            ->getFont()
            ->setBold(true)
            ->setName('Verdana')
            ->setSize($fontSize);

        // 表格坐标
        $cellIndex = $this->getColumn($column);
        foreach ($header as $index => $name) {
            $sheet->setCellValue($cellIndex[$index] . '1', $name);
        }

        $baseRow = 2;
        foreach ($data as $k => $v) {
            $i = $k + $baseRow;
            for ($k = 0; $k <= $column - 1; $k++) {
                $item = $v[$k];
                $sheet->setCellValue($cellIndex[$k] . $i, ' ' . $item);
                // 中文设置表格宽度
                if (preg_match("/[\x7f-\xff]/", $v[$k])) {
                    $sheet->getColumnDimension($cellIndex[$k])->setWidth(strlen($item));
                } else {
                    // 非中文自动设置宽度
                    $sheet->getColumnDimension($cellIndex[$k])->setAutoSize(true);
                }
            }
        }
        $sheet->calculateColumnWidths();

        return IOFactory::createWriter($spreadsheet, 'Xls');
    }

    /**
     * 分片保存csv
     *
     * @param integer $i 文件下标
     * @param array $header 头
     * @param array $data 数据
     * @param string $filename 文件名
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.5.31 17:04
     */
    public static function slicesCsv(int $i, array $header, array $data, string $filename)
    {
        //不限制执行时间，以防超时
        set_time_limit(0);
        // buffer计数器
        $cnt = 0;
        // 缓冲区
        $limit = 2000;
        // 生成临时文件
        $path = $filename.'_tmp';
        if($i == 0) {
            mkdir($path);
        }
        $fp = fopen($path.'/'.$filename . '_' . $i . '.csv', 'w');
        //第一次执行时将表头写入
        if($i == 0){
            fputcsv($fp, $header);
        }
        foreach ($data as $k=>$v) {
            $cnt++;
            //执行下一次循环之前清空缓冲区
            if ($limit == $cnt) {
                if( ob_get_level() > 0 ) ob_flush();
                $cnt = 0;
            }
            //每行写入到临时文件
            fputcsv($fp, $v);
        }
        fclose($fp);  //每生成一个文件关闭
    }

    /**
     * 合并csv文件
     *
     * @param string $filename
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.5.31 17:07
     */
    public static function mergeCsv(string $filename)
    {
        $path = $filename.'_tmp';
        $fileList = scandir($path);
        //将所有临时文件合并成一个
        foreach ($fileList as $file){
            $file = $path.'/'.$file;
            //如果是文件，提出文件内容，写入目标文件
            if(is_file($file)){
                //打开临时文件
                $tmpFile = fopen($file,'r');
                //读取临时文件
                if($str = fread($tmpFile,filesize($file))){
                    //关闭临时文件
                    fclose($tmpFile);
                    //打开或创建要合并成的文件，往末尾插入的方式添加内容并保存
                    $tmpFile2 = fopen($filename.'.csv','a+');
                    //写入内容 解决乱码问题
                    fwrite($tmpFile2,chr(0xEF).chr(0xBB).chr(0xBF));
                    if(fwrite($tmpFile2, $str)){
                        //关闭合并的文件，避免浪费资源
                        fclose($tmpFile2);
                    }
                }
            }
        }

        //将文件压缩，避免文件太大，下载慢
        $filenameZip = $filename . ".zip";
        $zip = new \ZipArchive();
        $zip->open($filenameZip, \ZipArchive::CREATE);   //打开压缩包
        $zip->addFile($filename.'.csv', basename($filename.'.csv'));   //向压缩包中添加文件
        $zip->close();  //关闭压缩包
        unlink($filename.'.csv');
        foreach ($fileList as $file) {
            if(is_file($path.'/'.$file)) {
                unlink($path.'/'.$file); //删除csv临时文件
            }
        }
        rmdir($path);
    }
}
