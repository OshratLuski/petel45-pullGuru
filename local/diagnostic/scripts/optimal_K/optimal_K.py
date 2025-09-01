# basic imports
import os
import pandas as pd
from pprint import pprint
import numpy as np
import scipy
import scipy.signal
import time
import warnings
warnings.filterwarnings('ignore')
import random
import sklearn
from joblib import Parallel, delayed
from collections import namedtuple
import matplotlib.pyplot as plt
# customize figures
plt.rc('text', usetex=True)
plt.rc('font', family='serif')
fontsize=12
plt.rcParams.update({'font.size': fontsize, "text.usetex": True})
plt.rcParams['figure.figsize'] = [6.0, 4.5]
plt.rc('xtick', labelsize=fontsize)
plt.rc('ytick', labelsize=fontsize)

from itertools import cycle
cycol = cycle('bgrcmyk')


# custom imports
import optimal_K.helpers as helpers


class OptimalK_Calculator():
    '''Module for calculating the optimal value of K.
    '''

    def __init__(self, project_dir="~", data_loaded_check=False, verbose=False, data_type = None, data_name = None,  gap_res=None, wgap_res=None, dd_gap_res=None, dd_wgap_res=None, optimizer_run_check=False, optk_output_csv_created_check=False, optk_output_csv_loc = None, gapwss_output_csv_loc = None, gapwss_output_csv_created_check = None, frac = None, gap_res_output_csv_loc=None, wgap_res_output_csv_loc=None, dd_gap_res_output_csv_loc=None, dd_wgap_res_output_csv_loc=None):
        self.project_dir = os.getcwd()
        self.data_loaded_check = False #default: no data has been loaded
        self.verbose = verbose
        self.data_type = None #update this when we load data
        self.data_name = None #if real data; change to dataset name. if synthetic; change to parameters
        self.gap_res=None
        self.wgap_res=None
        self.dd_gap_res=None
        self.dd_wgap_res=None
        self.optimizer_run_check = False
        self.optk_output_csv_created_check = False #default: csv has not been made
        self.gapwss_output_csv_created_check = False #default: csv has not been made
        self.optk_output_csv_loc = None
        self.gapwss_output_csv_loc = None
        self.frac = None
        self.gap_res_output_csv_loc=None
        self.wgap_res_output_csv_loc=None
        self.dd_gap_res_output_csv_loc=None
        self.dd_wgap_res_output_csv_loc=None
        pass


    def load_real_data(self, data_loc, filename, pre_cols, post_cols ):
        '''Loads in dataset from real student data csv file.

        Args:
            data_loc (str): location to directory of student csv.
            filename (str): filename of the student csv
            pre_cols (int): number of columns in the csv to ignore from the LEFT (e.g ID, course number...)
            post_cols (int): number of columns in the csv to ignore from the RIGHT (e.g. previous cluster ID)

        Returns:
            data (pd df): a dataframe of the student data.
        '''
        if self.verbose:
            print("Loading real student data from existing file.")
            print("Location/directory : {0}".format(data_loc))
            print("Filename : {0}".format(filename))

        # check if data has already been loaded
        if self.data_loaded_check == True:
            print("Data has already been loaded for this instance. Exiting program!")
            quit()

        # update the datatype
        self.data_type = 'real'
        self.data_name = str(filename[:-4])

        # read data in, obtain col headers, extract specified columns
        data = pd.read_csv(data_loc+filename)
        col_headers = data.columns
        data = data.iloc[:, pre_cols:len(col_headers)-post_cols]


        # update instance attribute to indicate we have already loaded data and cannot do this again.
        self.data_loaded_check = True

        return data


    def generate_synthetic_data(self, synthetic_data_type, n_clusters, n_features, n_rows, p_slip = 0, p_guess = 0):
        '''Generates synthetic data based on provided parameters.

        Args:
            synthetic_data_type (str): choose what kind of data we want to create.
            n_clusters (int): number of clusters within the simulated datasset
            n_features (int): number of features/questions within the simulated dataset
            n_rows (int): number of observations/students within the simulated dataset
            p_slip (int, optional): probability of slipping (getting question wrong even if they know the answer). Defaults to 0.
            p_guess (int, optional): probability of guessing (getting question right even if they don't know the answer). Defaults to 0.

        Returns:
            data (pd.df): a dataframe of the student data.
        '''

        # check if data has already been loaded
        if self.data_loaded_check == True:
            print("Data has already been loaded for this instance. Exiting program!")
            quit()

        # update data_type
        self.data_type = 'synthetic'

        # create data according to what kind of synthetic data we want
        if synthetic_data_type == "random_noise":

            self.data_name = 'noise_nc{0}_nf{1}_ns_{2}'.format(n_clusters, n_features, n_rows)
            # all random noise
            data = np.random.randint(0, 2, size=(int(n_rows), int(n_features)))

        if synthetic_data_type == "well_clustered":

            self.data_name = 'WC_nc{0}_nf{1}_ns_{2}'.format(n_clusters, n_features, n_rows)
            # generate initial matrix as clean data (all correct)
            data = np.ones((int(n_rows), int(n_features)))

            # each cluster has row_frac members and struggles with col_frac questions
            row_frac = round(n_rows/n_clusters)
            col_frac = round(n_features/n_clusters)

            #initialise to zero to work our way across/down the array
            row_count = 0
            feature_count = 0

            def _mask_applier(data, row_frac, col_frac, failure_chance):
                mask_list = random.choices([0, 1], weights=[failure_chance, 1-failure_chance], k=row_frac*col_frac)
                # reshape into specified dimensions
                mask = np.reshape(mask_list, (int(row_frac), int(col_frac)))

                # overwrite specific elements of the data with this mask
                data[row_count:row_count+row_frac, feature_count:feature_count+col_frac] = mask

                return data


            for i in np.arange(n_clusters):
                # give students a fixed % chance of failing on the specified questions even if they know it for a minute amount of noise
                failure_chance = 0.97
                try:
                    data = _mask_applier(data, row_frac, col_frac, failure_chance)

                except ValueError: #catch any end point cases
                    col_frac = col_frac - 1
                    row_frac = row_frac - 1
                    data = _mask_applier(data, row_frac, col_frac, failure_chance)

                # update the count to start from the new rows next time
                row_count +=row_frac
                feature_count +=col_frac

        if synthetic_data_type == "well_clustered_uneven":

            self.data_name = 'WC_uneven_nc{0}_nf{1}_ns_{2}'.format(n_clusters, n_features, n_rows)
            # generate initial matrix as clean data (all correct)
            data = np.ones((int(n_rows), int(n_features)))

            # each cluster has row_frac members and struggles with col_frac questions

            print(n_clusters)
            # cheap way to sum find the total sum of all these clusters
            nclus_frac = sum(np.arange(n_clusters+1))
            # print(sum(np.arange(n_clusters+1)))

            # exit(0)

            row_frac = round(n_rows/nclus_frac)
            # retain even number of clusters in each
            col_frac = round(n_features/n_clusters)

            #initialise to zero to work our way across/down the array
            row_count = 0
            feature_count = 0

            def _mask_applier(data, row_start, row_end, col_frac, failure_chance):
                row_size = int(row_end - row_start)
                # print(row_size)
                # exit()
                mask_list = random.choices([0, 1], weights=[failure_chance, 1-failure_chance], k=row_size*col_frac)
                # reshape into specified dimensions
                mask = np.reshape(mask_list, (int(row_size), int(col_frac)))

                # overwrite specific elements of the data with this mask
                data[int(row_start):int(row_end), feature_count:feature_count+col_frac] = mask

                return data



            print("total student rows: {0}".format(n_rows))
            print("total clusters: {0}".format(n_clusters))


            row_tot_counted = 0
            for i in np.arange(n_clusters):


                clus_stu_weight = i+1


                rows_clus_r = row_frac * clus_stu_weight


                if i == 0:
                    row_start = row_tot_counted
                else:
                    # row_start = row_tot_counted+1
                    row_start = row_tot_counted

                row_tot_counted += rows_clus_r

                if clus_stu_weight == n_clusters:
                    row_end = n_rows
                else:
                    row_end = row_tot_counted

                print(r"Cluster {0} allocated rows {1} to {2}".format(i+1, row_start, row_end))
                print(clus_stu_weight)

                # give students a fixed % chance of failing on the specified questions even if they know it for a minute amount of noise
                failure_chance = 0.97
                failure_chance = 1.0
                try:
                    data = _mask_applier(data, row_start, row_end, col_frac, failure_chance)

                except ValueError: #catch any end point cases
                    col_frac = col_frac - 1
                    row_frac = row_frac - 1
                    data = _mask_applier(data, row_start, row_end, col_frac, failure_chance)

                # update the count to start from the new rows next time
                row_count +=row_frac
                feature_count +=col_frac

        if synthetic_data_type == "realistic_slipguess":

            self.data_name = 'realistic_nc{0}_nf{1}_ns_{2}'.format(n_clusters, n_features, n_rows)

            # generate background - all correct, but account for chances of slipping
            stu_matrix = random.choices([0, 1], weights=[p_slip, 1-p_slip], k=int(n_rows)*int(n_features))
            data = np.reshape(stu_matrix, (int(n_rows), int(n_features)))

            row_frac = round(n_rows/n_clusters)
            col_frac = round(n_features/n_clusters)

            def _mask_applier(data, row_frac, col_frac, p_guess):
                mask_list = random.choices([0, 1], weights=[1-p_guess, p_guess], k=row_frac*col_frac)
                # reshape into specified dimensions
                mask = np.reshape(mask_list, (int(row_frac), int(col_frac)))

                # overwrite specific elements of the data with this mask
                data[row_count:row_count+row_frac, feature_count:feature_count+col_frac] = mask

                return data

            #initialise to zero to work our way across/down the array
            row_count = 0
            feature_count = 0
            for i in np.arange(n_clusters):
                # overwrite the relevant columsn/rows in the array, with a fixed chance of guessing

                try:
                    data = _mask_applier(data, row_frac, col_frac, p_guess)
                except ValueError: #catch any end point cases
                    col_frac = col_frac - 1
                    row_frac = row_frac - 1
                    data = _mask_applier(data, row_frac, col_frac, p_guess)

                # update the count
                row_count +=row_frac
                feature_count +=col_frac

        if synthetic_data_type == "realistic_slipguess_uneven":

            self.data_name = 'realistic_uneven_nc{0}_nf{1}_ns_{2}'.format(n_clusters, n_features, n_rows)
            # generate initial matrix as clean data (all correct)

            # each cluster has row_frac members and struggles with col_frac questions

            # print(n_clusters)
            # cheap way to sum find the total sum of all these clusters
            nclus_frac = sum(np.arange(n_clusters+1))
            # print(sum(np.arange(n_clusters+1)))

            # generate background - all correct, but account for chances of slipping
            stu_matrix = random.choices([0, 1], weights=[p_slip, 1-p_slip], k=int(n_rows)*int(n_features))


            data = np.reshape(stu_matrix, (int(n_rows), int(n_features)))

            row_frac = round(n_rows/nclus_frac)
            # retain even number of clusters in each
            col_frac = round(n_features/n_clusters)

            #initialise to zero to work our way across/down the array
            row_count = 0
            feature_count = 0

            def _mask_applier(data, row_start, row_end, col_frac, p_guess):
                row_size = int(row_end - row_start)
                # print(row_size)
                # exit()
                mask_list = random.choices([0, 1], weights=[1-p_guess, p_guess], k=row_size*col_frac)
                # reshape into specified dimensions
                mask = np.reshape(mask_list, (int(row_size), int(col_frac)))

                # overwrite specific elements of the data with this mask
                data[int(row_start):int(row_end), feature_count:feature_count+col_frac] = mask

                return data




            row_tot_counted = 0
            for i in np.arange(n_clusters):


                clus_stu_weight = i+1


                rows_clus_r = row_frac * clus_stu_weight

                if i == 0:
                    row_start = row_tot_counted
                else:
                    row_start = row_tot_counted+1

                row_tot_counted += rows_clus_r

                if clus_stu_weight == n_clusters:
                    row_end = n_rows
                else:
                    row_end = row_tot_counted

                print(r"Cluster {0} allocated rows {1} to {2}".format(i+1, row_start, row_end))
                print(clus_stu_weight)

                # give students a fixed % chance of failing on the specified questions even if they know it for a minute amount of noise
                try:
                    data = _mask_applier(data, row_start, row_end, col_frac, p_guess)
                except ValueError: #catch any end point cases
                    col_frac = col_frac - 1
                    row_frac = row_frac - 1
                    data = _mask_applier(data, row_start, row_end, col_frac, p_guess)

                # update the count to start from the new rows next time
                row_count +=row_frac
                feature_count +=col_frac


        data = pd.DataFrame(data)

        # update instance attribute to indicate we have already loaded data and cannot do this again.
        self.data_loaded_check = True


        # exit()

        return data


    def gap_statistics(self, data, clust_method, nmin = 1, nmax = 15, distance_measure = 'euclidean', nbootstraps = 280, parallel=True, nprocs = 4, frac = None):
        '''Performs the clustering, calculates relevant dispersions/statistics, and identifies the optimum value of k using gap statistics, weighted gap statistics, and relevant DD-adjacent neighbour comparisons.

        Args:
            data (pd.df): raw data of students (rows) and binary question performance (columns).
            clust_method (str): calls a particular clustering method
            nmin (int, optional): min k to consider. Defaults to 1.
            nmax (int, optional): max k to consider. Defaults to 15.
            distance_measure (str, optional): distance measure to use. Defaults to 'euclidean'.
            nbootstraps (int, optional): number of bootstraps to generate. Defaults to 280.
            parallel (bool, optional): True if we want to enable parallelism in the calculations. Defaults to True.
            nprocs (int, optional): Number of CPU procs to use. Only used if parallel is True. Defaults to 4.
            frac (_type_, optional): Fraction of the dataset we have taken. Defaults to None.

        Returns:
            Results (NamedTuples): NamedTuples of each of the 4 methods we consider in gap statistics; gap, weighted gap, dd-gap, dd-weighted gap. Each tuple contains all the information of interest from each method.
        '''

        if self.verbose is True:
            print("Data shape: {0}".format(data.shape))

        # update the switch to allow results to be plotted after this.
        self.optimizer_run_check = True

        # update the class switch so we know what fraction of the dataset we took (if applicable). Keep as none is no fraction taken.
        if frac is not None:
            self.frac = frac

        #* Define range of nclusts to test
        nclust_array = np.arange(nmin, nmax+1)
        # nclust_array = [4]


        #* Define empty arrays of specific shape to fill with data for each value of k that we calculate
        # gap (with logarithm) - Tibshirani 2001
        gap_k_array = np.zeros(len(nclust_array))
        s_k_array = np.zeros(len(nclust_array))
        w_k_array = np.zeros(len(nclust_array))

        # weighted gap (normal) - Yan 2007
        weighted_gap_k_array = np.zeros(len(nclust_array))
        weighted_s_k_array = np.zeros(len(nclust_array))
        weighted_w_k_array = np.zeros(len(nclust_array))

        # DD-weighted gap - Yan 2007
        dd_weighted_gap_k_array = np.zeros(len(nclust_array))

        # DD-normal gap - applying Yan 2007 to Tibshirani
        dd_gap_k_array = np.zeros(len(nclust_array))

        # bootstrapped data arrays
        wstar_k_avg_array = np.zeros(len(nclust_array))
        weighted_wstar_k_avg_array = np.zeros(len(nclust_array))


        #* Begin calculations
        for i in np.arange(len(nclust_array)):
            nclust = nclust_array[i]
            t = time.localtime()
            loctime = time.strftime("%H:%M:%S", t)

            if self.verbose:
                print("")
                print(r"Number of clusters: {0}. Started at time: {1}".format(nclust, loctime))

            '''Step 1) Perform bootstrapping of the data to generate a reference distribution: (W_k^*).'''
            # Tibshirani 2001 - "we estimate <Expectation of log(W_k^*)>by taking <average of the B copies of log(W_k^*)>""

            # simple for loop if no parallelism
            if parallel == False:
                # generate an array to store all boostrapped W_k values: fixed length of the number of bootstraps
                boostrapped_Wkstar = np.zeros(nbootstraps)
                boostrapped_weighted_Wkstar = np.zeros(nbootstraps)

                # repeat the boostrap sampling n_boostrap times
                for b in np.arange(nbootstraps):
                    print("Calculating bootstrap: {}".format(b))
                    # samples from the total dataset with replacement, to get reference of length k

                    bootstrapped_data = np.random.randint(2, size=data.shape)
                    bootstrapped_data = pd.DataFrame(bootstrapped_data)

                    # calculate WSS for this bootstrapped data
                    boostrapped_Wkstar[b], boostrapped_weighted_Wkstar[b] = helpers.WSS_calculator(bootstrapped_data, nclust, clust_method, verbosity=False, distance_measure = distance_measure)

            # accelerated parallelism with joblib
            if parallel:
                def _par_bootstrap_func(b, data, nclust, clust_method):
                    if clust_method == 'kmodes':
                        bootstrapped_data = np.random.randint(2, size=data.shape)
                    if clust_method == 'kmeans':
                        # NOTE: for binary data, we randomly choose the bootstrap sample to also be binary - we do NOT draw uniformly over the range, c.f. Tibshirani.
                        # TODO: add some check if input data is binary: draw uniformly if continuous, draw random binary if binary
                        # bootstrapped_data = np.random.uniform(low=0, high=1, size=data.shape)
                        bootstrapped_data = np.random.randint(2, size=data.shape)
                    else:
                        # default to drawing random integers
                        bootstrapped_data = np.random.randint(2, size=data.shape)

                    # convert to dataframe
                    bootstrapped_data = pd.DataFrame(bootstrapped_data)

                    # calculate WSS for this bootstrapped data
                    WSS_k_b, weighted_WSS_k_b = helpers.WSS_calculator(bootstrapped_data, nclust, clust_method, verbosity=False,  distance_measure = distance_measure)

                    return WSS_k_b, weighted_WSS_k_b

                if self.verbose:
                    print("Bootstrapping in parallel with {} CPUs...".format(nprocs))

                res = Parallel(n_jobs=nprocs)(delayed(_par_bootstrap_func)(b,  data, nclust, clust_method ) for b in (np.arange(nbootstraps)))

                boostrapped_Wkstar=[item[0] for item in res]
                boostrapped_weighted_Wkstar=[item[1] for item in res]


            '''Step 2) Calculations on original data (not bootstrapping)'''
            if self.verbose:
                print("Calculating WSS on original dataset (not bootstrap)...")

            # calculate WSS with helper function
            W_k, weighted_W_k = helpers.WSS_calculator(data, nclust, clust_method, verbosity=False,  distance_measure = distance_measure)

            # add real/bootstrapped results to each array.
            w_k_array[i] = W_k
            weighted_w_k_array[i] = weighted_W_k

            wstar_k_avg_array[i] = np.mean(boostrapped_Wkstar)
            weighted_wstar_k_avg_array[i] = np.mean(boostrapped_weighted_Wkstar)


            ##########################################

            '''Step 3a) Calculate gap statistic for this k value - Tibshirani; with logs'''
            if self.verbose:
                print("Calculating gap statistics...")
            log_boostrap_WKstar = np.log(boostrapped_Wkstar)

            # calculate mean of bootstraps
            mean_logbooststrap_WKstar = np.mean(log_boostrap_WKstar)

            # calculate gap statistic for this k (nclust)
            gap_k = mean_logbooststrap_WKstar - np.log(W_k)

            # calculate standard deviation for this k
            sd_k = np.std(log_boostrap_WKstar)

            # calculate related variable to standard error
            s_k = sd_k * np.sqrt( 1 + 1/nbootstraps )

            # add to specific entry in array
            gap_k_array[i] = gap_k
            s_k_array[i] = s_k

            '''3b) Weighted gap-statistics - Yan 2007 (WGap)'''
            log_weighted_boostrap_WKstar = np.log(boostrapped_weighted_Wkstar)

            # calculate mean of bootstraps
            mean_log_weighted_booststrap_WKstar = np.mean(log_weighted_boostrap_WKstar)

            # calculate gap statistic for this k (nclust)
            weighted_gap_k = mean_log_weighted_booststrap_WKstar - np.log(weighted_W_k)

            # calculate standard deviation for this k
            weighted_sd_k = np.std(log_weighted_boostrap_WKstar)

            # calculate related variable to standard error
            weighted_s_k = weighted_sd_k * np.sqrt( 1 + 1/nbootstraps )

            # add to specific entry in array
            weighted_gap_k_array[i] = weighted_gap_k
            weighted_s_k_array[i] = weighted_s_k

        ##########################################

        t = time.localtime()
        loctime = time.strftime("%H:%M:%S", t)
        if self.verbose:
            print("All clusters calculated at {0}.".format(loctime))

        ##########################################

        #* Calculate DD adjacant comparisons from the gap
        #* Determine optimum predictions for the DD-methods.

        '''4a) DD-weighted gap-statistics - Yan 2007 (DD-WGap)'''
        # Is only defined for k>2: keep k=1 as zero, and go up to n_clust - 1

        for i in np.arange(start=1, stop=len(weighted_gap_k_array)-1):
            yan_dd_crit = 2*weighted_gap_k_array[i] - weighted_gap_k_array[i-1] - weighted_gap_k_array[i+1]
            dd_weighted_gap_k_array[i] = yan_dd_crit

        max_index = np.argmax(dd_weighted_gap_k_array)
        local_max_index = scipy.signal.argrelextrema(dd_weighted_gap_k_array, np.greater)
        # convert the produced tuple to an array
        local_max_index = np.asarray(local_max_index)

        dd_weighted_gap_k_array[0] = None
        dd_weighted_gap_k_array[-1] = None

        '''4b) DD-gap-statistics - Applying DD-idea to vanilla gap statistics (DD-Gap)'''

        for i in np.arange(start=1, stop=len(gap_k_array)-1):
            yan_like_dd_crit = 2*gap_k_array[i] - gap_k_array[i-1] - gap_k_array[i+1]
            dd_gap_k_array[i] = yan_like_dd_crit

        custom_max_index = np.argmax(dd_gap_k_array)
        local_custom_max_index = scipy.signal.argrelextrema(dd_gap_k_array, np.greater)
        # convert the produced tuple to an array
        local_custom_max_index = np.asarray(local_custom_max_index)
        dd_gap_k_array[0] = None
        dd_gap_k_array[-1] = None

        ##########################################

        '''4c) Determine optimum predictions for vanilla gap'''
        std_err_fac = 1 #tibshirani proposal
        diff = []
        opt_i_indices = []
        for i in np.arange(len(nclust_array) -1):
            k = nclust_array[i]
            difference = gap_k_array[i] - gap_k_array[i+1] + std_err_fac*s_k_array[i+1]

            if difference > 0:
                opt_i_indices.append(i)

            diff.append(difference)

        try:
            first_i = opt_i_indices[0]
        except IndexError:
            first_i = -1 #if no criteria is found from method, then output the last one.

        ##########################################

        '''4d) Determine optimum predictions for weighted gap'''

        # diff = np.zeros_like(nclust_array)
        weighted_diff = []
        weighted_opt_i_indices = []
        for i in np.arange(len(nclust_array) -1):
            k = nclust_array[i]
            weighted_difference = weighted_gap_k_array[i] - weighted_gap_k_array[i+1] + std_err_fac*weighted_s_k_array[i+1]


            if weighted_difference > 0:
                weighted_opt_i_indices.append(i)

            weighted_diff.append(weighted_difference)
            # exit()

        try:
            weighted_first_i = weighted_opt_i_indices[0]
        except IndexError:
            weighted_first_i = -1


        '''5) Write results in named tuples'''

        #* Create named tuples for each of the four methods
        gap_res = namedtuple('gap', 'frac nclust_array gap_k_array s_k_array w_k_array wstar_k_avg_array optK_pred_indx criterion_array')

        wgap_res = namedtuple('wgap', 'frac nclust_array weighted_gap_k_array weighted_s_k_array weighted_w_k_array weighted_wstar_k_avg_array optK_pred_indx weighted_criterion_array')


        dd_gap_res = namedtuple('dd_gap', 'frac nclust_array dd_gap_k_array optK_pred_indx optK_localmax_indxs')
        dd_wgap_res = namedtuple('dd_wgap', 'frac nclust_array dd_weighted_gap_k_array optK_pred_indx optK_localmax_indx')

        #* Update class with the results: default was None to prevent plotting before running the actual clustering
        # self.gap_res = gap_res(self.frac, np.array(nclust_array), np.array(gap_k_array), np.array(s_k_array), np.array(w_k_array), np.array(wstar_k_avg_array), first_i, np.array(diff))
        # self.gap_res = gap_res(self.frac, nclust_array, gap_k_array, s_k_array, w_k_array, wstar_k_avg_array, first_i, diff)

        # self.wgap_res = wgap_res(self.frac, nclust_array, weighted_gap_k_array, weighted_s_k_array, weighted_w_k_array, weighted_wstar_k_avg_array, weighted_first_i, weighted_diff)

        # self.dd_gap_res = dd_gap_res(self.frac, nclust_array, dd_gap_k_array, custom_max_index, local_custom_max_index )

        # self.dd_wgap_res = dd_wgap_res(self.frac, nclust_array, dd_weighted_gap_k_array, max_index, local_max_index )

        #* need to save all numpy arrays as lists, such that it is easier to deal with the exports to csv later
        #self.gap_res = gap_res(self.frac, nclust_array.tolist(), gap_k_array.tolist(), s_k_array.tolist(), w_k_array.tolist(), wstar_k_avg_array.tolist(), first_i, diff)

#         self.wgap_res = wgap_res(self.frac, nclust_array.tolist(), weighted_gap_k_array.tolist(), weighted_s_k_array.tolist(), weighted_w_k_array.tolist(), weighted_wstar_k_avg_array.tolist(), weighted_first_i, weighted_diff)
#
#         self.dd_gap_res = dd_gap_res(self.frac, nclust_array.tolist(), dd_gap_k_array.tolist(), custom_max_index, local_custom_max_index.tolist())
#
        self.dd_wgap_res = dd_wgap_res(self.frac, nclust_array.tolist(), dd_weighted_gap_k_array.tolist(), max_index, local_max_index.tolist() )

        return self.dd_wgap_res


    def plot_results(self, linestyle, marker, markersize, nmin, nmax, image_dir=None, extended_results=False):
        '''Generates figure showing the results of data. Optional arg to pass to show an 'extended figure', including internal results such as WSS and optimal selection criterion.

        Args:
            linestyle (str): Define the linestyle.
            marker (str): Define the marker.
            markersize (int): Define the marker size.
            nmin (int): Min k to plot
            nmax (int): Max k to plot
            image_dir (str, optional): Location to save figure. Defaults to None.
            extended_results (bool, optional): True if output figure should contain WSS and optimal selection criterion. Defaults to False.
        '''

        # check that the optimizer has been run and we have results to plot.
        if self.optimizer_run_check==False:
            print("Model/optimization has not been run yet. Exiting!")
            quit()

        # set the colour for this plot
        col = next(cycol)

        #* create figure for comparing all gap values on a single summary figure of the gap plots
        if extended_results:
            # need 4x2 panels
            sum_fig, ((wss_ax, weighted_wss_ax), (gap_ax, wgap_ax), (ddgap_ax, ddwgap_ax), (opt_ax, weighted_op_ax)) = plt.subplots(figsize=(10, 12), nrows = 4, ncols=2, tight_layout=True)

        else:
            # need 2x2 panels
            sum_fig, ((gap_ax, wgap_ax), (ddgap_ax, ddwgap_ax)) = plt.subplots(figsize=(10, 6), nrows = 2, ncols=2, tight_layout=True)

        # label axes and set limits, add style choices like grid lines
        gap_ax.set_xlabel(r"Number of clusters, $k$")
        gap_ax.set_ylabel(r"Gap")

        wgap_ax.set_xlabel(r"Number of clusters, $k$")
        wgap_ax.set_ylabel(r"Weighted Gap")

        ddgap_ax.set_xlabel(r"Number of clusters, $k$")
        ddgap_ax.set_ylabel(r"DD--Gap")

        ddwgap_ax.set_xlabel(r"Number of clusters, $k$")
        ddwgap_ax.set_ylabel(r"DD--Weighted Gap")

        gap_ax.set_xlim(nmin, nmax)
        gap_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

        wgap_ax.set_xlim(nmin, nmax)
        wgap_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

        ddgap_ax.set_xlim(nmin, nmax)
        ddgap_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

        ddwgap_ax.set_xlim(nmin, nmax)
        ddwgap_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

        gap_ax.xaxis.grid(True, color ="grey", linestyle="dotted")
        wgap_ax.xaxis.grid(True, color ="grey", linestyle="dotted")
        ddgap_ax.xaxis.grid(True, color ="grey", linestyle="dotted")
        ddwgap_ax.xaxis.grid(True, color ="grey", linestyle="dotted")


        gap_ax.tick_params(axis="both",direction="in")
        wgap_ax.tick_params(axis="both",direction="in")
        ddgap_ax.tick_params(axis="both",direction="in")
        ddwgap_ax.tick_params(axis="both",direction="in")

        # mutual nclust array for all plots
        nclust_array = self.gap_res.nclust_array

        '''1) Vanilla gap'''
        # unpack values of interest
        gap_arr = self.gap_res.gap_k_array
        gap_optK_index = self.gap_res.optK_pred_indx

        # plot all
        gap_ax.plot(nclust_array, gap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col )
        # plot the max in red
        gap_ax.plot(nclust_array[gap_optK_index], gap_arr[gap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')


        '''2) Weighted gap'''

        wgap_arr = self.wgap_res.weighted_gap_k_array
        wgap_optK_index = self.wgap_res.optK_pred_indx

        # plot all
        wgap_ax.plot(nclust_array, wgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # plot the max in red
        wgap_ax.plot(nclust_array[wgap_optK_index], wgap_arr[wgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')


        '''3) DD-Vanilla gap'''
        ddgap_arr = self.dd_gap_res.dd_gap_k_array
        ddgap_optK_index = self.dd_gap_res.optK_pred_indx

        # plot all
        ddgap_ax.plot(nclust_array, ddgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # plot the max in red
        ddgap_ax.plot(nclust_array[ddgap_optK_index], ddgap_arr[ddgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')


        '''4) DD-Weighted gap'''
        self.dd_wgap_res
        ddwgap_arr = self.dd_wgap_res.dd_weighted_gap_k_array
        ddwgap_optK_index = self.dd_wgap_res.optK_pred_indx

        # plot all
        ddwgap_ax.plot(nclust_array, ddwgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # plot the max in red
        ddwgap_ax.plot(nclust_array[ddwgap_optK_index], ddwgap_arr[ddwgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')


        if extended_results:
            '''Plot additional information on other axes.'''

            # label additional axes, same style choices
            wss_ax.set_xlabel(r"Number of clusters, $k$")
            wss_ax.set_ylabel(r"$W_k$")

            weighted_wss_ax.set_xlabel(r"Number of clusters, $k$")
            weighted_wss_ax.set_ylabel(r"Weighted $W_k$")

            opt_ax.set_xlabel(r"Number of clusters, $k$")
            opt_ax.set_ylabel(r"Opt. Criterion")

            weighted_op_ax.set_xlabel(r"Number of clusters, $k$")
            weighted_op_ax.set_ylabel(r"Opt. Criterion")

            wss_ax.set_xlim(nmin, nmax)
            wss_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

            weighted_wss_ax.set_xlim(nmin, nmax)
            weighted_wss_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

            opt_ax.set_xlim(nmin, nmax)
            opt_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

            weighted_op_ax.set_xlim(nmin, nmax)
            weighted_op_ax.set_xticks(np.arange(nmin, nmax+1, step=1))

            '''plot the WSS and optimal criterions for vanilla gap'''

            gap_wk_arr = self.gap_res.w_k_array
            gap_wkstar_arr = self.gap_res.wstar_k_avg_array
            gap_criterion_diffarr = self.gap_res.criterion_array

            # plot both the data WSS and the bootstrap WSS*
            wss_ax.plot(nclust_array, gap_wk_arr, marker=marker, markersize=markersize, linestyle="-", color = col)
            wss_ax.plot(nclust_array, gap_wkstar_arr, marker='.', markersize=markersize, linestyle="--", color = col)

            # plot the optimization criterion
            opt_ax.bar(nclust_array[:-1], gap_criterion_diffarr, color = col)


            '''plot the WSS and optimal criterions for weighted gap'''

            wgap_wk_arr = self.wgap_res.weighted_w_k_array
            wgap_wkstar_arr = self.wgap_res.weighted_wstar_k_avg_array
            wgap_criterion_diffarr = self.wgap_res.weighted_criterion_array

            # plot both the data WSS and the bootstrapp WSS*
            weighted_wss_ax.plot(nclust_array, wgap_wk_arr, marker=marker, markersize=markersize, linestyle="-", color = col)
            weighted_wss_ax.plot(nclust_array, wgap_wkstar_arr, marker='.', markersize=markersize, linestyle="--", color = col)

            # plot the optimization criterion
            weighted_op_ax.bar(nclust_array[:-1], wgap_criterion_diffarr, color = col)

        # if no additional image_dir has been supplied, save to default location
        if image_dir is None:
            def_loc = self.project_dir + "/images/"
            if not os.path.isdir(def_loc):
                os.makedirs(def_loc)

            sum_fig.savefig(def_loc +"summary_optK_results.pdf", bbox_inches='tight')

        else:
            sum_fig.savefig(image_dir+"/summary_optK_results.pdf", bbox_inches='tight')

        return


    def output_optK_results(self, outputs_dir_loc):
        # check that the optimizer has been run and we have results to output.
        if self.optimizer_run_check==False:
            print("Model/optimization has not been run yet. Exiting!")
            quit()

        t = time.localtime()
        locdatetime = time.strftime("%y%m%d_%H_%M_%S", t)


        # check if the csv has been created already
        if self.optk_output_csv_created_check == False:
            # create location/name of output csv - only one time
            output_csv = outputs_dir_loc + r"/optKdata_type_{0}_{1}.csv".format(self.data_type, self.data_name)

            # update switch and loc
            self.optk_output_csv_created_check = True
            self.optk_output_csv_loc = output_csv

            # make the csv and write initial header
            header = ['frac', 'gap', 'wgap', 'dd-gap', 'dd-wgap', 'dd-wgap_locmax']

            df_to_write = pd.DataFrame([header])

            df_to_write.to_csv( self.optk_output_csv_loc, mode='a', index=False, header=False)


        # extract all optimalK information of interest to output

        # convert back to array to work with arrays where needed
        nclust_array = np.asarray(self.gap_res.nclust_array)

        gap_arr = self.gap_res.gap_k_array
        gap_optK_index = self.gap_res.optK_pred_indx
        gap_optK =  nclust_array[gap_optK_index]

        wgap_arr = self.wgap_res.weighted_gap_k_array
        wgap_optK_index = self.wgap_res.optK_pred_indx
        wgap_optK =  nclust_array[wgap_optK_index]

        ddgap_arr = self.dd_gap_res.dd_gap_k_array
        ddgap_optK_index = self.dd_gap_res.optK_pred_indx
        ddgap_optK =  nclust_array[ddgap_optK_index]

        ddwgap_arr = self.dd_wgap_res.dd_weighted_gap_k_array
        ddwgap_optK_index = self.dd_wgap_res.optK_pred_indx
        ddwgap_optK =  nclust_array[ddwgap_optK_index]

        ddwgap_locmax_indx = self.dd_wgap_res.optK_localmax_indx
        ddwgap_optK_locmax =  nclust_array[ddwgap_locmax_indx]

        # define fraction: use external arg, or if none supplied, assume to be 1 (complete dataset)
        if self.frac is None:
            frac = 1
        else:
            frac = self.frac

        # write to csv
        res = [frac, gap_optK, wgap_optK, ddgap_optK, ddwgap_optK, ddwgap_optK_locmax ]
        df_to_write = pd.DataFrame([res])

        df_to_write.to_csv(self.optk_output_csv_loc, mode='a', index=False, header=False)

        return


    def output_gap_results(self, outputs_dir_loc):
        '''Outputs the results of the frac, nclust_arr, WSS_k (both data and bootstrap), gap_k, and sd_k for each of the methods to distinct csv files. '''

        # check that the optimizer has been run and we have results to output.
        if self.optimizer_run_check==False:
            print("Model/optimization has not been run yet. Exiting!")
            quit()

        # check if the csv has been created already - create for each field if doesn't exist
        if self.gapwss_output_csv_created_check == False:
            # create location/name of output csv - only one time

            # update switch and loc
            self.gapwss_output_csv_created_check = True

            # create csv for each of the different methods to store different results
            #TODO: find an alternative rather than manually updating the csv locations for each and storing in the class attrs. Maybe some variable approach?
            for result in [self.gap_res, self.wgap_res, self.dd_gap_res, self.dd_wgap_res]:
                class_name = type(result).__name__
                unique_fields = result._fields

                # create an output csv for each method (gap, wgap, ddgap, ddwgap); can call this later on depending on class_name
                output_csv = outputs_dir_loc + r"/{2}_results_data_type_{0}_{1}.csv".format(self.data_type, self.data_name, class_name)

                if class_name == 'gap':
                    self.gap_res_output_csv_loc = output_csv

                if class_name == 'wgap':
                    self.wgap_res_output_csv_loc = output_csv

                if class_name == 'dd_gap':
                    self.dd_gap_res_output_csv_loc = output_csv

                if class_name == 'dd_wgap':
                    self.dd_wgap_res_output_csv_loc = output_csv

                df_to_write = pd.DataFrame([unique_fields])

                df_to_write.to_csv(output_csv, mode='a', index=False, header=False)

        # add each result to its designed output location
        for result in [self.gap_res, self.wgap_res, self.dd_gap_res, self.dd_wgap_res]:
            class_name = type(result).__name__

            if self.frac is None:
                frac = 1
            else:
                frac = self.frac

            # overwrite the first None element with the updated value.
            result = result._replace(frac=frac)

            if class_name == 'gap':
                output_csv = self.gap_res_output_csv_loc

            if class_name == 'wgap':
                output_csv = self.wgap_res_output_csv_loc

            if class_name == 'dd_gap':
                output_csv = self.dd_gap_res_output_csv_loc

            if class_name == 'dd_wgap':
                output_csv = self.dd_wgap_res_output_csv_loc

            # import ipdb; ipdb.set_trace()

            df_to_write = pd.DataFrame([result])
            df_to_write.to_csv(output_csv, mode='a', index=False, header=False)

        return



#     ########################################
#     # Original datasets: 2 chem, 2 phys
#     ########################################

#     # small chemistry
#     specific_data_file = "response-matrix-10787.csv"
#     pre_cols, post_cols = 3, 1

#     # # large chemistry
#     # specific_data_file = "med-large-diagnostic-fixedcolumn.csv"
#     # pre_cols, post_cols = 3, 1

#     # large physics
#     specific_data_file = "response-matrix-ph-309449-large.csv"
#     pre_cols, post_cols = 3, 1

#     # small physics
#     # specific_data_file = "response-matrix-ph-268707-med.csv"
#     # pre_cols, post_cols = 3, 1

#     ########################################
#     # additional 5 chemistry files from Yael - all small; some have prev. clustering, so be careful of removing the correct rows
#     ########################################

#     # # 10788 - pre-clustered
#     # specific_data_file = "yael_chem/response-matrix-ch-10788-small.csv"
#     # pre_cols, post_cols = 3, 1

#     # # 10790 - not clustered
#     # specific_data_file = "yael_chem/response-matrix_10790.csv"
#     # pre_cols, post_cols = 3, 0

#     # # 11047 - not clustered
#     # specific_data_file = "yael_chem/response-matrix_11047.csv"
#     # pre_cols, post_cols = 3, 0

#     # # stochiometry - 2 extra cols
#     # specific_data_file = "yael_chem/edit_headers_small_Stoichiometry.csv"
#     # pre_cols, post_cols = 3, 2

#     # # atomic structure - 2 extra cols
#     # specific_data_file = "yael_chem/edit_headers_structure_of_the_atom-small.csv"
#     # pre_cols, post_cols = 3, 2


