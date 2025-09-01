#!/usr/bin/env python
# This Python file uses the following encoding: utf-8

import json
import sys
import random as rand
import os
import pip
import pandas
import xml.dom.minidom
import numpy as np
from zeep import Client
from gensim.models import Word2Vec
from keras.models import Sequential, load_model
from keras.layers import LSTM, Dense, Dropout, Masking, Embedding, Activation, Flatten
from keras.layers import SpatialDropout1D, Bidirectional, Conv1D, MaxPooling1D, GlobalMaxPooling1D
from keras.callbacks import ModelCheckpoint, EarlyStopping, TensorBoard
from tensorflow.keras.optimizers import Adam, RMSprop


def extract_words_embeddings(dictionaryWords):
    word_embeddings_matrix = np.zeros([len(dictionaryWords) + 1, 100])
    for key, index in dictionaryWords.items():
        basic_form = key[0].replace('&', '')
        if basic_form in w2v.wv:
            word_embeddings_matrix[index] = w2v.wv[basic_form]
    return word_embeddings_matrix


def create_model(embedding_dim=100, num_filters=128,
                 kernel_size=5, dense_size=128,
                 vocab_size=None,
                 maxlen=None,
                 embedding_matrix=None):
    model = Sequential()
    model.add(Embedding(vocab_size, embedding_dim, input_length=maxlen,
              weights=[embedding_matrix], trainable=True))
    model.add(SpatialDropout1D(0.2))

    model.add(Conv1D(num_filters, kernel_size,
              activation='relu', padding="same"))
    model.add(MaxPooling1D(2))
    model.add(Conv1D(num_filters, kernel_size,
              activation='relu', padding="same"))
    model.add(GlobalMaxPooling1D())

    model.add(Dense(dense_size, activation='relu'))
    model.add(Dropout(0.5))
    model.add(Dense(1, activation='sigmoid'))

    model.compile(loss='binary_crossentropy', optimizer=Adam(
        learning_rate=0.001), metrics=['accuracy'])

    return model


def replaceCOforms(text):
    out = text.replace(u'CO', co_hebrew)
    out = out.replace(u'co', co_hebrew)
    out = out.replace(u'Co', co_hebrew)
    out = out.replace(u'פח\״ח', co_hebrew)
    out = out.replace(u'פחח', co_hebrew)
    out = out.replace(u'הפחח', co_hebrew)
    out = out.replace(u'הפח\״ח', co_hebrew)

    # out = out.replace(u'ATP',atp_hebrew)
    return out


# Press the green button in the gutter to run the script.
if __name__ == '__main__':
    outputfilepath = ''
    moodledatapath = ''
    textfilepath = ''
    question_attempt = ''
    categoriesids = ''

    # path = ''
    # question_attempt = ''
    # text = ''
    if len(sys.argv) > 1:
        outputfilepath = sys.argv[1]
        moodledatapath = sys.argv[2]
        textfilepath = sys.argv[3]
        question_attempt = sys.argv[4]
        categoriesids = sys.argv[5]
        num_models = sys.argv[6]
        num_models = int(num_models)

        # path = sys.argv[1]
        # question_attempt = sys.argv[2]
        # text = sys.argv[3]
        print('using data from task')

    else:
        print('ERROR!!')
    if outputfilepath == '' or moodledatapath == '' or textfilepath == '' or question_attempt == '' or categoriesids == '':
        # print('ERROR!!')
        print('using DUMB data')
        # TODO: remove DUMB

        outputfilepath = ''
        moodledatapath = ''
        textfilepath = ''
        question_attempt = ''
        categoriesids = ''

        # path = ''
        # question_attempt = ''
        # text = ''
        # category = ''

        # category = ["B", "C", "A", "D", "E", "F",
        # "G", "J", "H", "I", "K", "L", "M"]


    # category = ''

    # if category and category != '':
    #     categories = category.split('|')
    #     categories.pop()
    #     i = 0

#    categories = []

    to_return = {}

    # TODO: categories ids

    client = Client(
        "https://hlp.nite.org.il/WebServices/WS_NITE_TextTaggerHeb.asmx?WSDL")
    w2v = Word2Vec.load(
        moodledatapath + "/open-question-models/embeddings/wiki.he.word2vec.cbow.model")
    max_answer_len = 370
    dictionaryWords = ''
    with open(moodledatapath + "/open-question-models/dictionary.txt", encoding='utf-8', mode='r') as f:
        for i in f.readlines():
            dictionaryWords = i  # string
    # this is orignal dict with instace dict
    dictionaryWords = eval(dictionaryWords)
    embedding_matrix = extract_words_embeddings(dictionaryWords)
    os.chdir(moodledatapath + "/open-question-models/models/")
    model = create_model(embedding_dim=100, num_filters=128,
                         kernel_size=5, dense_size=128,
                         vocab_size=len(dictionaryWords) + 1,
                         maxlen=max_answer_len,
                         embedding_matrix=embedding_matrix)

    ############
    # text = u"בסיגריה נמצא הגז המזיק פחמן חד חמצני, המשתחרר בעת העישון ובעל נטייה חזקה יותר משל החמצן להקשר להמוגלובין. היקשרות החמצן להמוגלובין חיונית עבור הובלה תקינה של חמצן,  ולכן היקשרות של פחמן חד חמצני להמוגלובין על חשבון היקשרות של החמצן אליו תגרום להובלה לא תקינה של החמצן לתאי הגוף, ובכך התאים לא יקבלו מספיק חמצן עבור תהליך הנשימה התאית שמפיק אנרגיה, פעילות גופנית היא פעולה שדורשת הרבה אנרגיה ולכן המצאות הפחמן החד חמצני ימנע את הפקת האנרגיה הדרושה לפעילות גופנית."
    # text = u"המוגלובין הוא חלבון קושר חמצן המצוי בתאי הדם האדומים, באמצעותם מובל החמצן לתאי הגוף. הגז CO (פחמן חד-חמצני) המשתחרר מהסיגריה נקשר גם הוא להמוגלובין, והקישור שלו להמוגלובין חזק יותר מהחמצן. לכן, בנוכחות CO, יותר CO יקשר להמוגלובין שבתאי הדם האדומים. בשל כך כמות החמצן שתיקשר להמוגלובין תהיה קטנה יותר ורמת החמצן בדם תרד. ירידה ברמת החמצן בדם גורמת לירידה בכמות החמצן הזמין לתאי הגוף/שריר. בתאי הגוף מופקת אנרגיה הדרושה לפעילות התאים בתהליך הנשימה התאית. מכיוון שחמצן דרוש לתהליך הנשימה התאית, הירידה ברמת החמצן בדם גורמת לירידה בקצב הנשימה התאית / לפחות נשימה תאית, מה שמוביל לירידה בכמות האנרגיה המופקת בתאים. האנרגיה המופקת בתאים דרושה לפעילות גופנית, ומכאן הקושי של המעשנים לבצע פעילות גופנית."
    ##########

    # TODO: Save input data.

    # Input
    # print('write input data file start')
    # input_data_filename = "i_" + str(question_attempt) + '.log'
    # input_data_full_path = os.path.join(path, input_data_filename)

    categoriesids = json.loads(categoriesids)
    num_categories = len(categoriesids) # 11 # len(categories)

    print('INPUT data: =================')
    print('outputfilepath: ' + outputfilepath)
    print('moodledatapath: ' + moodledatapath)
    print('textfilepath: ' + textfilepath)
    print('question_attempt: ' + str(question_attempt))
   # print('category: ' + str(categories))
    print('categoriesids: ' + str(categoriesids))
    print('number of category: ' + str(num_categories))
    print('=============================')

    # with open(input_data_full_path, 'w') as f:
    #     f.write(path + '\n')
    #     f.write(str(question_attempt) + '\n')
    #     f.write(text + '\n')
    #     f.write(str(categories) + '\n')
    #     f.close()
    # print(input_data_full_path)
    # print('write input data file end')

    # TODO: read textanswer
    with open(textfilepath, 'r') as f:
        text = f.read()
        f.close()

    print('text = ' + str(text))

    co_hebrew = u'פחמן חד חמצני'
    atp_hebrew = u"אנרגיה"
    co_english = [u'CO', u'co']
    text = replaceCOforms(text)
    responce = client.service.TagText(text)
    doc = xml.dom.minidom.parseString(responce)
    tokens = doc.getElementsByTagName("Token")
    words = []
    for token in tokens:
        if (token.getAttribute("type") != "NL"):
            orgWord = token.firstChild.data
        else:
            orgWord = "."
        word = orgWord
        if (token.getAttribute("type") == "W") and (token.getAttribute("sub") == "H"):
            node = token.firstChild.nextSibling
            if node.getAttribute("basic_form") != "":
                word = (node.getAttribute("basic_form"),
                        node.getAttribute("pos"))
                words.append(word)
    answer = np.zeros([1, max_answer_len])
    index = 0
    for word in words:
        if index < max_answer_len:
            if word in dictionaryWords:
                answer[0, index] = dictionaryWords[word]
        index = index + 1

#     num_models = 1
    scores = {}
    for i in categoriesids:
        score = 0
        for j in range(0, num_models):
            model.load_weights(str(i) + str(j))
            # score = score + np.round(model.predict(answer)[0][0], 0)
            score = score + model.predict(answer)[0][0]

        print('----')
        print('categoryid ' + str(i))
        # print('index => ' + str(i))
        print('raw score ')
        print(score)
        # print('raw score / num_models ')
        # print(score / num_models)

        score = np.round(score / num_models, 0)
        print('float ' + str(score))
        #score = np.int_(score / num_models)
        print('int   ' + str(score))
        scores[i] = score
        # to_return.append(score)
        to_return[i] = score

    # print(scores[category])
    print('to return: ' + str(to_return))

    print('write output data file start')

    print('OUTPUT data: =================')
    print('scores: ' + str(to_return))
    print('=============================')

    with open(outputfilepath, 'w') as f:
        # f.write(str(to_return))
        # f.write(json.dumps(to_return))
        json.dump(to_return, f)
        f.close()

    # os.chmod(outputfilepath, 0o777)
    print(outputfilepath)
    print('write output data file end')
