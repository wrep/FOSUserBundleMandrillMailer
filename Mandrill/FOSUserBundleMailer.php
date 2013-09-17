<?php

/*
 * This file is part of the Wrep\FOSUserBundleMandrillMailer
 *
 * (c) Rick Pastoor <rick@wrep.nl>
 *
 */

namespace Wrep\FOSUserBundleMandrillMailer\Mandrill;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface,
	Symfony\Component\Routing\RouterInterface,
	FOS\UserBundle\Model\UserInterface,
	FOS\UserBundle\Mailer\MailerInterface;

/**
 * Mailer implementation for the FOSUserBundle
 *
 * @author Mathijs Kadijk <mathijs@wrep.nl>
 */
class FOSUserBundleMailer implements MailerInterface
{
	/**
     * @var \Symfony\Component\Routing\RouterInterface
     */
	protected $router;

	/**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
	protected $templating;

	/**
	 * Mandrill dispatcher to use for sending
	 *
     * @var \Hip\MandrillBundle\Dispatcher
     */
	protected $dispatcher;

	/**
	 * Mandrill message to use for sending
	 *
     * @var \Hip\MandrillBundle\Message
     */
	protected $message;

	/**
	 * Email templates to use and other parameters
	 *
     * @var array
     */
	protected $parameters;

	/**
     * Constructor
     *
     * @param RouterInterface	$router
     * @param EngineInterface 	$templating
     * @param Dispatcher		$dispatcher
     * @param Message 			$message
     * @param array      		$parameters
     */
	public function __construct(RouterInterface $router, EngineInterface $templating, $dispatcher, $message, array $parameters)
	{
		$this->router = $router;
		$this->templating = $templating;
		$this->dispatcher = $dispatcher;
		$this->message = $message;
		$this->parameters = $parameters;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendConfirmationEmailMessage(UserInterface $user)
	{
		$template = $this->parameters['confirmation.template'];

		$url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
		$rendered = $this->templating->render($template, array(
				'confirmationUrl' =>  $url,
				'user' => $user
		));

		$this->sendEmailMessage($rendered, $user->getEmail());
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendResettingEmailMessage(UserInterface $user)
	{
		$template = $this->parameters['resetting_password.template'];

		$url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
		$rendered = $this->templating->render($template, array(
			'confirmationUrl' => $url,
			'user' => $user
		));

		$this->sendEmailMessage($rendered, $user->getEmail());
	}

	/**
	 * This will configure the message and send it
	 *
	 * @param string    $renderedTemplate
	 * @param string    $toEmail
	 */
	protected function sendEmailMessage($renderedTemplate, $toEmail)
	{
		// Split subject and body
		$renderedLines = explode("\n", trim($renderedTemplate));
		$subject = $renderedLines[0];
		$body = implode("\n", array_slice($renderedLines, 1));

		// Check e-mail content
		if (strlen($body) == 0 || strlen($subject) == 0) {
			throw new \RuntimeException(
					"No message was found, cannot send e-mail to " . $toEmail . ". This " .
					"error can occur when you don't have set a confirmation template or using the default " .
					"without having translations enabled."
			);
		}

		// Send message via Mandrill
		$this->message->addTo($toEmail);
		$this->message->setSubject($subject);
		$this->message->setText($body);
		$this->message->setTrackClicks(false);

		$this->dispatcher->send($this->message);
	}
}