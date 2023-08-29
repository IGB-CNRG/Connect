<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\DigestBuffer;
use App\Repository\DigestBufferRepository;
use App\Settings\SettingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'app:send-digest',
    description: 'Add a short description for your command',
)]
class SendDigestCommand extends Command
{
    public function __construct(
        private readonly DigestBufferRepository $digestBufferRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly SettingManager $settingManager,
        private readonly MailerInterface $mailer,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entryDigests = $this->digestBufferRepository->findBy(['bufferName' => DigestBuffer::ENTRY_BUFFER]);
        $exitDigests = $this->digestBufferRepository->findBy(['bufferName' => DigestBuffer::EXIT_BUFFER]);

        // Add people to email from digest buffer
        $subject = 'Entry/Exit Digest';
        try {
            $html = $this->twig->render('workflow/digest/entry_exit.html.twig', [
                'subject' => $subject,
                'entries' => $entryDigests,
                'exits' => $exitDigests,
            ]);

            $email = (new Email())
                ->from($this->settingManager->get('notification_from'))
                ->to($this->settingManager->get('digest_recipients'))
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);

            // remove people from the buffer if the email sent successfully
            foreach (array_merge($entryDigests, $exitDigests) as $digest){
                $this->digestBufferRepository->remove($digest);
            }
            $this->entityManager->flush();

            $io->success('Digest sent!');

            return Command::SUCCESS;
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            // Twig error
            // display the error and exit
            dump($e);
        } catch (TransportExceptionInterface $e) {
            // Mailer error
            // display the error and exit
            dump($e);
        }

        return Command::FAILURE;
    }

}
