<?php
    include('./httpful.phar');

    # Your storage path, need to end with slash /
    $base_dir = './data/';

    # Board param needed
    if( ! isset($argv[1]) ){
        echo "No param given\n";
        return;
    }
    echo "Will consult ".$argv[1]."\n";
    $board = $argv[1];

    if(!is_dir($base_dir.$board)){
        mkdir($base_dir.$board);
    }

    while(true){
        try{
            $r = \Httpful\Request::get("http://a.4cdn.org/".$board."/threads.json")
                ->autoparse(false)
                ->expectsJson()
                ->send();
        }catch(\Exception $e){
            echo "There was a problem consulting 4chan tread\n";
            sleep(15*60);
            continue;
        }

        sleep(2);
        $response = json_decode($r);
        foreach($response as $page){
            echo "Consulting page ".$page->page."\n";
                foreach ($page->threads as $key => $thread_main) {
                    $thread_id = $thread_main->no;
                    echo "Thread no. ".$thread_id."\n";
                    try{
                        $response_tread_raw = \Httpful\Request::get("http://a.4cdn.org/".$board."/thread/".$thread_id.".json")
                        ->autoparse(false)
                        ->expectsJson()
                        ->send();
                    }catch(\Exception $e){
                        echo "There was a problem consulting 4chan tread\n";
                        sleep(15*60);
                        continue;
                    }
                    $response_tread = json_decode($response_tread_raw);

                    if(isset($response_tread->posts))
                        foreach ($response_tread->posts as $key => $post) {
                            if($key == 0){
                                //create folder with no. identification
                                $title = $post->semantic_url;
                                if (!is_dir($base_dir.$board.'/'.$title)) {
                                    echo 'creating dir '.$title."\n";
                                    mkdir($base_dir.$board.'/'.$title);
                                }
                                echo "Consulting thread ".$title."\n";
                            }
                            if(isset($post->tim) && isset($post->ext)){
                            $url = 'http://i.4cdn.org/'.$board.'/'.$post->tim.$post->ext;
                            $path_to_save = $base_dir .$board.'/'. $title . '/' . $post->tim . $post->ext;
                            try {
                                if(!file_exists($path_to_save)) {
                                    file_put_contents($path_to_save, fopen($url, 'r'));
                                    sleep(1);
                                }
                            }catch(\Exception $e){echo "Exception\n";}
                      }
           }
           sleep(5);
       }
   }
   echo "Finished consulting all pages, will sleep for 15 mins";
   sleep(60*15);
}
