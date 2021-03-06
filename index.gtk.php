<?php
include_once 'lib/adianti/util/TAdiantiLoader.class.php';
spl_autoload_register(array('TAdiantiLoader', 'autoload_gtk'));

/**
 * TApplication Gtk Wrapper
 */
class TApplication extends GtkWindow
{
    const APP_TITLE = 'Adianti Framework :: Samples';
    static $inst;
    private $content;
    private $classname;
    
    /**
     * Constructor Method
     */
    function __construct()
    {
        parent::__construct();
        parent::set_size_request(840,640);
        parent::set_position(GTK::WIN_POS_CENTER);
        parent::connect_simple('delete-event', array($this, 'onClose'));
        parent::connect_simple('destroy', array('Gtk', 'main_quit'));
        parent::set_title(self::APP_TITLE);
        parent::set_icon(GdkPixbuf::new_from_file('favicon.png'));
        
        $gtk = GtkSettings::get_default();
        $gtk->set_long_property("gtk-button-images", TRUE, 0);
        $gtk->set_long_property("gtk-menu-images", TRUE, 0);
        
        self::$inst = $this;
        
        $ini  = parse_ini_file('application.ini');
        $lang = $ini['language'];
        TAdiantiCoreTranslator::setLanguage($lang);
        TApplicationTranslator::setLanguage($lang);
        date_default_timezone_set($ini['timezone']);
        
        $this->content = new GtkFixed;

        $vbox = new GtkVBox;
        parent::add($vbox);
        
        $vbox->pack_start(GtkImage::new_from_file('app/images/pageheader-gtk.png'), false, false);
        
        $MenuBar = TMenuBar::newFromXML('menu.xml');
        
        $vbox->pack_start($MenuBar, false, false);
        $vbox->pack_start($this->content, true, true);
        
        parent::show_all();
    }
    
    /**
     * Pack a class inside the application window
     * @param $class class name
     */
    function run($class)
    {
        if (class_exists($class))
        {
            $object = new $class;
            
            if ($object instanceof TPage)
            {
                if ($children = $this->content->get_children())
                {
                    foreach ($children as $child)
                    {
                        $this->content->remove($child);
                    }
                }
                
                $this->classname = $class;
                $this->content->put($object, 5, 5);
                $object->show();
                return $object;
            }
            else
            {
                $object->show();
                return $object;
            }
        }
    }
    
    /**
     * Execute a specific method of a class with parameters
     *
     * @param $class class name
     * @param $method method name
     * @param $parameters array of parameters
     */
    static public function executeMethod($class, $method, $parameters = NULL)
    {
        if (class_exists($class))
        {
            $inst = self::getInstance();
            $object = $inst->run($class);
            $object->$method($parameters);
        }
    }
    
    /**
     * on close the main window
     */
    function onClose()
    {
        $dialog = new GtkMessageDialog(null, Gtk::DIALOG_MODAL, Gtk::MESSAGE_QUESTION,
                                             Gtk::BUTTONS_YES_NO, 'Close the window ?');
        $dialog->set_position(GTK::WIN_POS_CENTER);
        $response = $dialog->run();
        if ($response == Gtk::RESPONSE_YES)
        {
            $dialog->destroy();
            return false;
        }
        else
        {
            $dialog->destroy();
            return true;
        }
    }
    
    /**
     * Returns the application instance
     */
    static function getInstance()
    {
        return self::$inst;
    }
}
ini_set('php-gtk.codepage', 'UTF8');
$app = new TApplication;

try
{
    Gtk::Main();
}
catch (Exception $e)
{
    $app->destroy();
    $ev=new TExceptionView($e);
    $ev->show();
    Gtk::main();
}
?>