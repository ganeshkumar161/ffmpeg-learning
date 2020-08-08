<html>
    <head>
        <title>Laravel</title>

        <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

    </head>
    <body>
        <div class="container" style="display:none;">
            <div class="content">
                <h1>File Upload</h1>
                <form action="{{ URL::to('text_upload_animate') }}" method="post" enctype="multipart/form-data">
                    
                    
                    <input type="hidden" name="timing" id="timing" value="0"> 
                    <label>Select image to upload:</label>
                    <input type="file" name="image" id="image"><br><br><br>

                    <label>Select video to upload:</label>
                    <input type="file" name="video" id="video"><br><br><br>

                    <input type="submit" value="Upload" name="submit">
                    <input type="hidden" value="{{ csrf_token() }}" name="_token">
                </form>

            </div>
        </div>



        <div class="container">
            <div class="content">

                <h1>Merge Video</h1>
                <form action="{{ URL::to('merge_video') }}" method="post" enctype="multipart/form-data">
                    
                    
                    <input type="hidden" name="timing" id="timing" value="0"> 
                    <label>Video 1:</label>
                    <input type="file" name="video1" id="video1"><br><br><br>

                    <label>video 2:</label>
                    <input type="file" name="video2" id="video2"><br><br><br>

                    <input type="submit" value="Upload" name="submit">
                    <input type="hidden" value="{{ csrf_token() }}" name="_token">
                </form>

            </div>
        </div>
    </body>
</html>