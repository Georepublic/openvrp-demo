<?php
class Login extends Controller {

	function Login()
	{
		parent::Controller();	
	}

	function index()
	{
	    if ($this->session->userdata('logged_in') == TRUE)
	    {
	        redirect('main');
	    }

	    $data['title'] = 'Login Page';
	    $data['language'] = 'en';
	    $data['username'] = array('id' => 'username', 'name' => 'username');
	    $data['password'] = array('id' => 'password', 'name' => 'password');	
	    
	    $this->load->view('login_view', $data);
	}

	function process()
	{
	    $username = $this->input->post('username');    
	    $password  = $this->input->post('password');

	    if ($username == 'daniel' AND $password == 'kastl')
	    {
	        $data = array(
                   'username'  => $username,
                   'logged_in'  => TRUE
                );

                $this->session->set_userdata($data);

                redirect('main');
	    } 
	    else 
	    {
	        $this->session->set_flashdata('message', "<b style='color:red;'>Oops, it seems your username or password is incorrect, please try again.</b>");
	        redirect('login');
	    }
	}

	function logout()
	{
	    $this->session->sess_destroy();

	    redirect('login');
	}
}
?>
