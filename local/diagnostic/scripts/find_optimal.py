import re
import csv
import sys
sys.path.append(".")
sys.path.append("..")

import optimal_K.optimal_K as optK
import multiprocessing
import optimal_K.helpers as helpers
import os
import time
import numpy as np
import json


def main(fullpath_file_name, pre_cols, post_cols, nmin, nmax, nprocs, fractional_run_validation_bool = False):
    t = time.localtime()
    loctime = time.strftime("%H:%M:%S", t)


    ###################################################
    #* Step 0: specify root directory and relevant ones.
    #* Default choice is the same directory as this runner.
    ###################################################
    # root_dir = os.getcwd()
    root_dir = os.path.realpath(os.path.dirname(__file__))   # location of the script.

    locdatetime = time.strftime("%y%m%d_%H-%M-%S", t)
    file_name = fullpath_file_name
    # check for/create master output directory and data directory
    parse_path = os.path.split(os.path.abspath(fullpath_file_name))

    data_dir = parse_path[0]+'/'
    file_name = parse_path[1]

    # If folders don't exist, then create
    outputs_dir = root_dir+"/outputs/"
    if not os.path.isdir(outputs_dir):
        os.makedirs(outputs_dir)

    ###################################################
    #* Step 1: create instance of the optimizer model.
    ###################################################
    opt_instance = optK.OptimalK_Calculator(verbose=False)

    #* Load in the dataset
    dataset = opt_instance.load_real_data(data_loc=data_dir, filename = file_name, pre_cols=pre_cols, post_cols=post_cols)

    # create local output dir with relevant information
    localoutput_dir = outputs_dir + "/{0}_data_{1}/".format(locdatetime, file_name[:-4])

    # Create output directories with helpful information
    image_dir = localoutput_dir+"/summary+images/"
    csv_dir = localoutput_dir+"/csv_output/"

    if not os.path.isdir(image_dir):
        os.makedirs(image_dir)

    if not os.path.isdir(csv_dir):
        os.makedirs(csv_dir)


    ###################################################
    #* Step 3: perform analysis to predict optimal K
    #* Specify clustering method, k-range to calculate, and number of multiprocessing cores
    ###################################################
    t = time.localtime()
    loctime = time.strftime("%H:%M:%S", t)

    # calculate gap
    clust_method = "kmeans"

    ddwgap_res = opt_instance.gap_statistics(data = dataset, clust_method = clust_method, nmin = nmin, nmax = nmax, nprocs = nprocs)

    # collect results to print out to terminal.
    nclust_array = np.arange(nmin, nmax+1)
#
#     wgap_ind_pred = wgap_res.optK_pred_indx
#     wgap_k_pred = nclust_array[wgap_ind_pred]

    ddwgap_ind_pred = ddwgap_res.optK_pred_indx
    ddwgap_localmax_ind_pred = ddwgap_res.optK_localmax_indx

    ddwgap_k_pred = nclust_array[ddwgap_ind_pred]
    ddwgap_localmax_k_pred = nclust_array[ddwgap_localmax_ind_pred]

    return ddwgap_localmax_k_pred



if __name__=="__main__":
    st = time.time()

    ###################################################
    #* Human input: define file_name, column range, kmin and kmax
    ###################################################

    #* 1) set min and max k range to consider (always go from 1 up to X)
    nmin  = sys.argv[2] if  len(sys.argv) >= 3 else 1
    nmax = sys.argv[3] if  len(sys.argv) == 4 else 10
    nmin = int(nmin)
    nmax = int(nmax)


    #* 2) set the number of parallel processors
    nprocs = 1
    if multiprocessing.cpu_count()>2 :
        nprocs = multiprocessing.cpu_count() - 2

    #* 3) set the file name
    #* 4) define the number of columns to remove from the start/left (e.g. UserID) and end/right (e.g. previous clustering) of the csv file

    file_name = sys.argv[1]
    pre_cols, post_cols = 0, 0

    #* 5) run the code
    array_to_return = main(file_name, pre_cols, post_cols, nmin, nmax, nprocs)

    print(json.dumps(array_to_return.tolist()))
    # optional: provide a boolean to perform fractional slices of the dataset to check validity
    # main(file_name, pre_cols, post_cols, nmin, nmax, nprocs, fractional_run_validation_bool = False)

    st = time.time() - st
    st = st/60

