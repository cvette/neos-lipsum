<?php
namespace Vette\Lipsum\Fusion;

use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * Lipsum Implementation
 */
class LipsumImplementation extends AbstractFusionObject {

    const MIN_WORDS_PER_SENTENCE = 4;
    const MAX_WORDS_PER_SENTENCE = 10;

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

    protected $lastWord = '';

    /**
     * Evaluate this TypoScript object and return the result
     * @return string
     */
    public function evaluate()
    {
        $wordCount = $this->fusionValue('wordCount');
        $paragraphCount = $this->fusionValue('paragraphCount');
        $sentencesPerParagraph = $this->fusionValue('sentencesPerParagraph');
        $textAlign = $this->fusionValue('textAlign');
        $startLipsum = $this->fusionValue('lipsum');

        $sentences = $this->createSentences($wordCount, $startLipsum);
        $paragraphs = $this->createParagraphs($sentences, $paragraphCount, $sentencesPerParagraph, $textAlign);

        return implode('', $paragraphs);
    }

    /**
     * Select a random word from array and make sure it isn't used twice in a row
     *
     * @return string
     */
    protected function randomWord()
    {
        $word = $this->latinWords[rand(0, count($this->latinWords) - 1)];

        // make sure next word is different
        if($word == $this->lastWord) {
            return $this->randomWord();
        }

        return $word;
    }

    /**
     * Create a sentence consisting of randomly selected words
     *
     * @param $length
     * @return string
     */
    protected function createSentence($length)
    {
        $sentence = array();

        $commaPos = rand(3, $length - 4);
        for($i = 0; $i < $length; $i++) {
            $word = $this->randomWord();

            // 50% chance of adding a comma to sentences longer than 6 words
            if($i == $commaPos && $length > 6 && rand(0,1) == 1) {
                $word .= ',';
            }

            $sentence[] = $word;
        }

        return ucfirst(implode(' ', $sentence)) . '.';
    }

    /**
     * @param $wordCount
     * @param $usedWordsCount
     * @return int
     */
    protected function getSentenceLength($wordCount, $usedWordsCount)
    {
        $length = rand($this::MIN_WORDS_PER_SENTENCE, $this::MAX_WORDS_PER_SENTENCE);

        //If there are less than MIN_WORDS_PER_SENTENCE words left, add them to the current sentence
        $rest = $wordCount - ($usedWordsCount + $length);
        if($rest < $this::MIN_WORDS_PER_SENTENCE) {
            $length += $rest;
        }

        return $length;
    }

    /**
     * Create an array of sentences with the given word count
     *
     * @param $wordCount
     * @param $startLipsum
     * @return array
     */
    protected function createSentences($wordCount, $startLipsum)
    {
        $usedWordsCount = 0;
        $sentences = array();

        if($startLipsum) {
            $sentences[] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
            $usedWordsCount += 8;
        }

        do {
            $length = $this->getSentenceLength($wordCount, $usedWordsCount);
            $sentences[] = $this->createSentence($length);
            $usedWordsCount += $length;

        } while ($usedWordsCount < $wordCount);

        return $sentences;
    }

    /**
     * Create a specified number of paragraphs from the given sentences array
     *
     * @param $sentences
     * @param $paragraphCount
     * @param $sentencesPerParagraph
     * @param $textAlign
     * @return array
     */
    protected function createParagraphs($sentences, $paragraphCount, $sentencesPerParagraph, $textAlign)
    {
        $paragraphs = array();
        for($i = 0; $i < $paragraphCount; $i++) {
            $length = $sentencesPerParagraph;

            if($i == ($paragraphCount - 1)) {
                $length = null;
            }

            $paragraphSentences = implode(' ', array_slice($sentences, $i * $sentencesPerParagraph, $length));
            $paragraphs[] = '<p style="text-align: ' . $textAlign . ';">' . $paragraphSentences . '</p>';
        }

        return $paragraphs;
    }
}
