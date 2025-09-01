########################
#README for find_optimal.py##
#######################
#Python Instalations

pip install joblib
pip install matplotlib
pip install numpy
pip install pandas
pip install pyclustering
pip install scikit_learn
pip install scipy
pip install seaborn

The script use 2 arguments -
1. full/path/to/file - absolute
2. nmax - max k to decide the range which kmean will be optimal

the script will print back JSON array of the optimal numbers to cluster with

To use the script from bash, use it like this:

python3 find_optimal.py [full/path/to/file] [nmax]
python3 local/diagnostic/scripts/find_optimal.py response-matrix-example-clean.csv /var/moodledata/local_diagnostic 1
