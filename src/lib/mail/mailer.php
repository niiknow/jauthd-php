<?php
namespace MyAPI\Lib\Mail;

class Mailer {
	protected $mailer, $container, $view;
	public function __construct($mailer, $container) {
		$this->mailer = $mailer;
		$this->container = $container;
		$this->view = $container->twig;
	}
	public function send($template, $data, $callback) {
		$message = new Message($this->mailer);
		$body = $this->view->render($template, [
			'data' => $data,
		]);

		$template = $this->view->loadTemplate($template);
		$subject = $template->renderBlock('subject', [
			'data' => $data,
		]);
		$message->body($body);
		$message->subject($subject);
		call_user_func($callback, $message);
		$this->mailer->send();
	}
}