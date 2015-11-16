<?php
namespace CV\Lipsum\TypoScript;

use TYPO3\Flow\Annotations;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

class LipsumImplementation extends AbstractTypoScriptObject {

    protected $latinWords =
        array('donec', 'at', 'posuere', 'est', 'in', 'volutpat', 'nam', 'egestas', 'tempus', 'turpis',
                'ac', 'gravida', 'morbi', 'faucibus', 'sapien', 'ut', 'erat', 'lacinia', 'commodo', 'nullam',
                'vestibulum', 'venenatis', 'leo', 'nec', 'pharetra', 'sed', 'blandit', 'urna', 'lorem', 'convallis',
                'quis', 'porta', 'rutrum', 'nulla', 'nibh', 'eros', 'etiam', 'magna', 'velit', 'tincidunt', 'ultrices',
                'et', 'libero', 'efficitur', 'felis', 'aliquet', 'ante', 'cursus', 'fusce', 'massa', 'non', 'odio',
                'id', 'fermentum', 'purus', 'maecenas', 'semper', 'dignissim', 'mauris', 'diam', 'neque',
                'suspendisse', 'arcu', 'sit', 'amet', 'elit', 'facilisi', 'bibendum', 'eget', 'ipsum', 'primis',
                'orci', 'luctus', 'cubilia', 'curae', 'vehicula', 'suscipit', 'viverra', 'pellentesque', 'ligula',
                'proin', 'ullamcorper', 'nisi', 'praesent', 'ultricies', 'lacus', 'scelerisque', 'maximus', 'vitae',
                'curabitur', 'sem', 'ex', 'hendrerit', 'euismod', 'nunc', 'tortor', 'eleifend', 'duis', 'eu', 'dui',
                'facilisis', 'dapibus', 'nisl', 'cras', 'rhoncus', 'consequat', 'aliquam', 'vel', 'quam', 'tristique',
                'sodales', 'tellus', 'phasellus', 'aenean', 'feugiat', 'vivamus', 'dictum', 'finibus', 'enim',
                'pulvinar', 'mi', 'malesuada', 'risus', 'auctor', 'pretium', 'augue', 'placerat', 'a', 'consectetur',
                'metus', 'justo', 'lectus', 'vulputate', 'congue', 'dolor', 'iaculis', 'varius', 'molestie',
                'condimentum', 'accumsan', 'porttitor', 'mollis', 'quisque', 'ornare', 'imperdiet', 'class', 'aptent',
                'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia', 'nostra', 'inceptos', 'himenaeos',
                'interdum', 'mattis', 'lobortis', 'cum', 'sociis', 'natoque', 'penatibus', 'magnis', 'dis',
                'parturient', 'montes', 'nascetur', 'ridiculus', 'mus', 'tempor', 'sollicitudin', 'fringilla',
                'elementum', 'sagittis', 'integer');

    const MIN_WORDS_PER_SENTENCE = 4;
    const MAX_WORDS_PER_SENTENCE = 10;

    protected $lastWord = '';

    private function randomWord() {
        $word = $this->latinWords[rand(0, count($this->latinWords) - 1)];

        // make sure next word is different
        if($word == $this->lastWord) {
            return $this->randomWord();
        }

        return $word;
    }

    private function createSentence($length) {
        for($i = 0; $i < $length; $i++) {
            $sentence[] = $this->randomWord();
        }

        return ucfirst(implode(' ', $sentence)) . '.';
    }

    private function createSentences($wordCount) {
        $usedWordsCount = 0;

        if($this->tsValue('lipsum')) {
            $sentences[] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
            $usedWordsCount += 8;
        }

        do {
            $length = rand($this::MIN_WORDS_PER_SENTENCE, $this::MAX_WORDS_PER_SENTENCE);

            //If there are less than MIN_WORDS_PER_SENTENCE words left, add them to the current sentence
            $rest = $wordCount - ($usedWordsCount + $length);
            if($rest < $this::MIN_WORDS_PER_SENTENCE) {
                $length += $rest;
            }

            $sentences[] = $this->createSentence($length);
            $usedWordsCount += $length;

        } while ($usedWordsCount < $wordCount);

        return $sentences;
    }

    /**
     * Evaluate this TypoScript object and return the result
     *
     * @return mixed
     */
    public function evaluate() {
        $wordCount = $this->tsValue('wordCount');
        $paragraphCount = $this->tsValue('paragraphCount');
        $sentencesPerParagraph = $this->tsValue('sentencesPerParagraph');
        $textAlign = $this->tsValue('textAlign');

        $sentences = $this->createSentences($wordCount);

        $paragraphs = array();
        for($i = 0; $i < $paragraphCount; $i++) {
            $length = $sentencesPerParagraph;
            if($i == ($paragraphCount - 1)) {
                $length = null;
            }

            $paragraphSentences = array_slice($sentences, $i * $sentencesPerParagraph, $length);
            $paragraphs[] = '<p style="text-align: ' . $textAlign . ';">' . implode(' ', $paragraphSentences) . '</p>';
        }

        return implode('', $paragraphs);
    }
}