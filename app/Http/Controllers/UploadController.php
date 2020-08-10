<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use FFMpeg;

use Image;

use Validator;

use File;

use Gumlet\ImageResize;

use wapmorgan\UnifiedArchive\UnifiedArchive;

use AYazdanpanah\SaveUploadedFiles\Exception\Exception;

use FFMpeg\Coordinate\Dimension;

use FFMpeg\Format\Video\DefaultVideo as BaseVideo;

use AYazdanpanah\SaveUploadedFiles\Upload;

class UploadController extends Controller
{
    //

    public function upload(Request $request)
    {
        try{

     
            $validator = Validator::make( $request->all(), array(
                'video'     => 'required|mimes:mkv,mp4,qt',
                'image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg',
                )
            );
            
            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                return back()->with('flash_errors', $error_messages);

            } 

            ///videos
            $file = $request->file('video');

            $file_name = time().rand(100,999).".".$file->getClientOriginalExtension();

            $path = public_path() . '/uploads/';

            $save_location = $path.$file_name;

            $file->move($path, $file_name);


            ////Images
            $photo = $request->file('image');

            $image_name = time().'.'.$photo->getClientOriginalExtension(); 

            $destination_path = $path."/images";

            $img = Image::make($photo->getRealPath())->resize(100, 100);

            $img->save($destination_path.'/'.$image_name,80);

            $photo->move($destination_path, $image_name);


            $watermark_path =  $destination_path.'/'.$image_name;
           

            // Top Left corner
            $top_left = "ffmpeg -i  ".$save_location." -i ".$watermark_path." -filter_complex ". '"\
            [1][0]scale2ref=h=ow/mdar:w=iw/8[#A video][image];\
            [#A video]format=argb,colorchannelmixer=aa=0.5[#B video transparent];\
            [image][#B video transparent]overlay\
            =(main_w-overlay_w)/(main_w-overlay_w):y=(main_h-overlay_h)/(main_h-overlay_h)"'." ".uniqid().$file_name;

            // Top Right corner
             $top_right = "ffmpeg -i   ".$save_location." -i ".$watermark_path." -filter_complex ". '"\
            [1][0]scale2ref=h=ow/mdar:w=iw/8[#A video][image];\
            [#A video]colorchannelmixer=aa=0.5[#B video transparent];\
            [image][#B video transparent]overlay\
            =(main_w-overlay_w):y=(main_h-overlay_h)/(main_h-overlay_h)"'." ".uniqid().$file_name;


            // bottom Right corner
            $bottom_right = "ffmpeg -i  ".$save_location." -i ".$watermark_path." -filter_complex ". '"\
            [1][0]scale2ref=h=ow/mdar:w=iw/8[#A video][image];\
            [#A video]colorchannelmixer=aa=0.5[#B video transparent];\
            [image][#B video transparent]overlay\
            =(main_w-w)-(main_w*0.1):(main_h-h)-(main_h*0.1)"'." ".uniqid().$file_name;

            // bottom Left corner
            $bottom_left = "ffmpeg -i  ".$save_location." -i ".$watermark_path." -filter_complex ". '"\
            [1][0]scale2ref=h=ow/mdar:w=iw/8[#A video][image];\
            [#A video]colorchannelmixer=aa=0.5[#B video transparent];\
            [image][#B video transparent]overlay\
            =(main_w-w)-(main_w*0.9):(main_h-h)-(main_h*0.1)"'." ".uniqid().$file_name;


            exec($top_left);
            exec($top_right);
            exec($bottom_right);
            exec($bottom_left);

            // // $text = "ffmpeg -i ".$path.$filename."  -ignore_loop  0  -i ".$watermark_path." -filter_complex ". '"\
            // // [1:v]format=yuva444p,scale=50:50,setsar=1,rotate=PI/10:c=black@0:ow=rotw(PI/12):oh=roth(PI/10) [rotate];[0:v][rotate] overlay=(main_w-overlay_w)/(main_w-overlay_w):y=(main_h-overlay_h)/(main_h-overlay_h):shortest=1"'." -codec:a copy -y "." ".uniqid().$filename;
            
            // exec($text);
            return response()->download($path.$file_name);
            // return $file->move($path, $filename);

        } catch(Exception $e) {

            return redirect()->back()->withInput()->with('flash_error' ,$e->getMessage());

        }    

    }





    public function video_watermark_text(Request $request)
    {

        try{
     
            $validator = Validator::make( $request->all(), [
                'video'     => 'required|mimes:mkv,mp4,qt',
                ]
            );

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                return back()->with('flash_errors', $error_messages);

            } 

            $timing = '';

            if($request->timing){

            // enable the filter from 12 seconds to 3 minutes:
            $timing = "enable='between(t,12\,3*60)':";
                
            }

            ///videos
            $file = $request->file('video');

            $filename = $file->getClientOriginalName();

            $path = public_path() . '/uploads/';

            $save_location = $path.$filename;

            $file->move($path, $filename);

            $watermark_text = $request->text??'Streamhash';

            
            $font_text = "fontfile=/path/to/font.ttf: \ text='$watermark_text': fontcolor=white: fontsize=14: box=1: boxcolor=black@0.5: \ boxborderw=5: x=(main_w-text_w)/(main_w-text_w):y=(main_h-text_h)/(main_h-text_h)";

            $format = "ffmpeg -i ".$save_location." -vf ".'"'."drawtext=".$timing.$font_text.'"'."   -codec:a copy"." ".uniqid().$filename;

            if($timing==''){

                $draw_text = '"'.$font_text.'"';

                $format = "ffmpeg -i ".$save_location." -vf drawtext=".$draw_text."   -codec:a copy"." ".uniqid().$filename;


            }



            exec($format);

            return response()->download($path.$filename);


            } catch(Exception $e) {
                    
                return redirect()->back()->withInput()->with('flash_error' ,$e->getMessage());

            }  

    }



    public function video_watermark_text_animate(Request $request)
    {
        try{
     
            $validator = Validator::make( $request->all(), [
                'video'     => 'required|mimes:mkv,mp4,qt',
                ]
            );

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                return back()->with('flash_errors', $error_messages);

            } 

            ///videos
            $file = $request->file('video');

            $filename = time().'.'.$file->getClientOriginalExtension(); 

            $path = public_path() . '/uploads/';

            $save_location = $path.$filename;

            $file->move($path, $filename);

            $watermark_text = $request->text??'Streamhash';

            $exec = "ffmpeg -i ".$save_location."  -filter:v drawtext=".'"'."fontfile=/usr/share/fonts/truetype/freefont/FreeSans.ttf:text='".$watermark_text."':fontcolor=white@1.0:fontsize=16:y=h-line_h-100:x=w/10*mod(t\,10):enable=gt(mod(t\,20)\,10)".'"'." -codec:v libx264 -codec:a copy -y"." ".uniqid().$filename;;

            exec($exec);

            return response()->download($path.$filename);


            } catch(Exception $e) {
                    
                return redirect()->back()->withInput()->with('flash_error' ,$e->getMessage());

            }  

    }



     public function merge_video_validation(Request $request){

        try {

            $video_path = public_path() . '/uploads/';

            $video = $request->file('video1');

            $video_name = str_random().".".$video->getClientOriginalExtension();

            $export_video_1 = $this->export_video($video_path,$video,$video_name);

             
            $video2 = $request->file('video2');

            $video2_name = str_random().".".$video2->getClientOriginalExtension();

            $export_video_2 = $this->export_video($video_path,$video2,$video2_name);
            
            if($export_video_1 && $export_video_2){

                return response()->download($video_path.$video_name);

            }

            } catch(Exception $e){
                
                return back()->with('flash_error',$e->getMessage());

            }

     } 



     public function export_video($video_path,$video_name,$filename){

        try 
        {

        $video = \FFMpeg\FFMpeg::create()->open($video_name);

        if(!in_array($video_name->getClientOriginalExtension(),video_types())){

            throw new Exception("This is not a valid video file extension");
        }

        $video_metadata = $video->getStreams()->videos()->first();
            
        if (!$video_metadata->isVideo() && null === ($duration = $video_metadata->get('duration')) && $duration >= 60) {
            
            throw new Exception("Your file is not a video or your video duration is longer than 1 minute!");
        }

        if ($video_metadata->get('width',1) * $video_metadata->get('height', 1) < 1280 * 740) {
            
            throw new Exception("Sorry, your video  ".$video_name->getClientOriginalName()."  must be at least HD or higher resolution");
        }

        if ($video_metadata->getDimensions()->getRatio()->getValue() == 16 / 9) {
           
            throw new Exception("Sorry, the video  ".$video_name->getClientOriginalName()."  ratio must be 16 / 9");
        }

        $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(intval($video_metadata->get('duration') / 4)))->save("$video_path/screenshots.jpg");
        
        $image = new ImageResize("$video_path/screenshots.jpg");
        
        $image->resizeToWidth(240)->save("$video_path/{$video_name}_screenshots_small.jpg");

        $video->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(3), new FFMpeg\Coordinate\Dimension(240, 95), 3)
            ->save("$video_path/{$video_name}_animation.gif");

        mkdir("$video_path/dash/$video_name", 0777, true);
        
        $video_name->move($video_path, $filename);

        @unlink($filename);

        return $video_metadata->all();

        } catch(Exception $e){
            
            // echo '<pre>';print_r($e->getMessage());die;
            return back()->with('flash_error',$e->getMessage());

        }

     }







    public function merge_video(Request $request)
    {

        try{
     
            $validator = Validator::make( $request->all(), [
                'video1'     => 'required|mimes:mkv,mp4,qt',
                'video2'     => 'required|mimes:mkv,mp4,qt',
                ]
            );

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                return back()->with('flash_errors', $error_messages);

            } 

            $file1 = $request->file('video1');

            $file2 = $request->file('video2');

            $file1_name = time().rand(100,999).".".$file1->getClientOriginalExtension();

            $file2_name = time().rand(100,999).".".$file2->getClientOriginalExtension();
            

            $path = public_path() . '/uploads/';

            $file1->move($path, $file1_name);

            $file2->move($path, $file2_name);
            
            $tsfile1 = time().rand(100,999).".ts";

            $tsfile2 = time().rand(100,999).".ts";

            $output = time().rand(100,999).".mp4";

            $first_conversion = "ffmpeg -i ".$path.$file1_name."  -c copy -bsf:a aac_adtstoasc ".$tsfile1;
            
            exec($first_conversion);

            $second_conversion = "ffmpeg -i ".$path.$file2_name." -c copy -bsf:a aac_adtstoasc ".$tsfile2;

            exec($second_conversion);

            $last = "ffmpeg -i"."  ".'"'."concat:".$tsfile1."|".$tsfile2. '"'." -c copy "."  ".$output;

            exec($last);

            unlink($path.$file1_name);  
            unlink($path.$file2_name);
            unlink(public_path()."/".$tsfile1);
            unlink(public_path()."/".$tsfile2);

            return response()->download(public_path()."/".$output);

        } catch(Exception $e) {
                    
            return redirect()->back()->withInput()->with('flash_error' ,$e->getMessage());

        }  

    }


}
