<?php



use Modules\Extend\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ExceptionSlack extends Notification implements ShouldQueue
{
    use Queueable;

    public $msg = '';

    /**
     * Create a new notification instance.
     */
    public function __construct(\Exception $e)
    {
        //ddd($e);
        $this->msg .= \chr(13).'Line    :    '.$e->getLine();
        $this->msg .= \chr(13).'File    :    '.$e->getFile();
        $this->msg .= \chr(13).'Url     :    '.URL::current();
        $this->msg .= \chr(13).'Msg     :    '.$e->getMessage();
        $this->msg .= \chr(13).'at      :    '.Carbon::now();
        $this->msg .= \chr(13).'ip      :    '.\Request::ip();;
        $this->msg .= \chr(13).'ip      :    '.$this->getIp();;
        if(isset($_SERVER['HTTP_REFERER'])){
            $this->msg .= \chr(13).'referer     :    '.$_SERVER['HTTP_REFERER'];
        }

        $this->msg .= \chr(13).'url prev:    '.\URL::previous();
        if (\Auth::check()) {
            $this->msg .= \chr(13).'user     :    '.\Auth::user()->handle;
        }
        $data = \json_encode(\Request::all(), JSON_PRETTY_PRINT);
        $this->msg .= \chr(13).'request : '.$data;
        //$this->msg.=chr(13).' -- debug backtrace --';
        //$this->msg.=chr(13).$e->getTraceAsString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $content = $this->msg; //$e->getMessage();
        return (new SlackMessage())
            ->content($content);
    }


    //--------
    public function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }//end getIp
}//end class
