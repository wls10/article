<?php 
/**
 * Article module media api
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) http://www.eefocus.com
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          
 * @since           1.0
 * @package         Module\Article
 */

namespace Module\Article;

use Pi;

class media
{
	public function getAttribute($id,$attribute)
	{
		$model = Pi::model('file','article');
		$rowset = $model->find($id);
		switch ($attribute){
			case "name":
				return $rowset["name"];
				break;
			case "type":
				return $rowset["type"];
			case "url":
				return $rowset["url"];
				break;
			case "size":
				return $rowset["size"];
				break;
			case "submitter":
				return $rowset["submitter"];
				break;
			case "time_upload":
				return $rowset["time_upload"];
				break;
			case "description":
				return $rowset["description"];
				break;
			default:
				return false;
		}
	}
	
	public function getFilestatistics($id,$statistics)
	{
		$model = Pi::model('file_statistics','article');
		$rowset = $model->find($id);
		switch ($statistics){
			case "num_article":
				return $rowset["num_article"];
				break;
			case "time_lastused":
				return $rowset["time_lastused"];
				break;
			case "num_download":
				return $rowset["num_download"];
				break;
			default:
				return false;
		}
	}
	
	public function getFileId($path)
	{
		$model = Pi::model('file','article');
		$rowset = $model->find($path, 'url');
		return $rowset["id"];
	}
	
	public function getUrl($file,$algo)
	{
		$dir = Pi::service('module')->config('path_media');
		$sub = Pi::service('module')->config('sub_dir_pattern');
		$dir = $dir.date($sub);
		if(strpos($file["type"], "image")===false){
			$dir = $dir."/image/";
		}
		else {
			$dir = $dir."/file/";
		}
		if (!file_exists($dir)){
			dirname($dir);
			mkdir($dir, 0777);
		}
		$result = microtime();	
		if (0 === strcasecmp($algo, 'md5')) {
			$result = md5($result);
		} else if (0 === strcasecmp($algo, 'sha1')) {
			$result = sha1($result);
		}
		$url = $dir.$result.$file["name"];
		return $url;
	}
	
	public function uploadFile($file,$descripe,$author)
	{
		$path = self::getUrl($file);
		if(move_uploaded_file($file["tmp_name"], $path)){
			$model = Pi::model('file','article');
			$data = array(
					'name'             => $file["name"],
			        'time_upload'      => date("Y-m-d H:i:s"),
			        'size'             => $file["size"],
			        'url'              => $path,
			        'description'      => $descripe
			);
			$row = $model->createRow($data);
			$row->save();
			if (!$row->id) {
				return false;
			}
			$id = self::getFileId($path);
			$newmodel = Pi::model('file_statistics','article');
			$statistics = array(
					'id'               => $id,
			);
			$newrow = $newmodel->createRow($statistics);
			$newrow->save();
			return true;
		}
		else {
			return false;
		}
	}
	
	public function downloadFile($id)
	{
		$path = self::getAttribute($id,"url");
		if(file_exists($path))
		{
			// 输入文件标签
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
			header("Content-Description:File Transfer");
			header ("Content-type: application/octet-stream");
			header ("Content-Disposition: attachment; filename=" . basename($path));
			readfile($path); 
			
			$model = Pi::model('file_statistics','article');
			$rowset = $model->find($id);
			$rowset["num_download"] += 1;
			$rowset->save();
		}
		else
		{
			echo("文件不存在!");
			exit;
		}
	}
	
	public function deleteFile($id)
	{
		$path = self::getAttribute($id,"url");
		@unlink($path);
		$model = Pi::model('file','article');
		$rowset = $model->find($id);
		$rowset->delete();
		$newmodel = Pi::model('file_statistics','article');
		$newrowset = $model->find($id);
		$newrowset->delete;
	}
}
?>