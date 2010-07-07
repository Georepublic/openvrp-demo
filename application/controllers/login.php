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
	    $data['username'] = array('id' => 'username', 'name' => 'username');
	    $data['password'] = array('id' => 'password', 'name' => 'password');	        
	    $this->load->view('login', $data);
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
	        $this->session->set_flashdata('message', '<div id="message">Oops, it seems your username or password is incorrect, please try again.</div>');
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
