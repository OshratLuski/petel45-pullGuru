# basic imports
import numpy as np
import matplotlib.pyplot as plt
import scipy
import os
import pandas as pd
import itertools
import seaborn as sns
import time

import warnings
warnings.filterwarnings('ignore')

from joblib import Parallel, delayed

import sklearn.cluster
from kmodes.kmodes import KModes

def WSS_calculator(data, nclust, clust_method, verbosity = True, distance_measure = 'euclidean'):
    '''Calculates the contributions of the WSS and the weighted WSS for a specific value of k.

    Args:
        data (pd.df or np.array): dataset to be clustered
        nclust (int): the number of clusters to find within the dataset
        clust_method (str): e.g. kmeans
        verbosity (bool, optional): Turn on information print. Defaults to True.
        distance_measure (str, optional): Select distance measure. Defaults to 'euclidean'.

    Returns:
        WSS and Weighted WSS: the Within-Cluster Sum of Squares for each of the weighted and the vanilla calculations. These results intended to be used in subsequent gap statistic calculations.
    '''


    total_students = len(data)
    student_labels = np.arange(0, total_students, 1) # +1


    # initialise empty list of size nclust - holds the clustered students
    clustered_students_array = [None] * nclust
    for i in np.arange(len(clustered_students_array)):
        # define each element of clustered_students_array to be a list (to be appended to)
        clustered_students_array[i] = []


    # Cluster the original total data to obtain with selected 'nclust' number of clusters - uses specified clust_method
    clusters, centroids = clusterer(data, clust_method, nclust, verbosity)

    # Separated students into the 'nclust' clusters
    for i in np.arange(len(clusters)):
        student_label = student_labels[i] #student number = student_label
        cluster_label = clusters[i] #cluster number = cluster_label
        # e.g. student 0 is in cluster 3...

        # append to the relevant array in the clustered_students_array
        clustered_students_array[cluster_label].append(student_label)

    if verbosity:
        print(clustered_students_array)

    # for each cluster; calculate the summed distance for all points in cluster r; contribute to WSS
    W_nclust = 0
    weighted_W_nclust = 0
    for r in np.arange(nclust):
        if verbosity:
            print("...")
            print("Calculating contribution to WSS from Cluster %r" %r)


        # extract only the students in cluster r
        cluster_r_students = clustered_students_array[r]
        # exit()
        cluster_r_data = data.iloc[cluster_r_students]
        centroid_r = centroids[r]

        # get information about the number of students/questions
        student_rows = cluster_r_data.shape[0] #number of rows, i.e. cardinality of the set
        question_cols = cluster_r_data.shape[1]
        if verbosity:
            print("Cluster {0} contains {1} members".format(r, student_rows))

        optimized_order_n = True
        # optimized_order_n = False
        if optimized_order_n: #calculate by comparing all elements in cluster to the centroid

            if distance_measure == 'euclidean':
                D_r_array = np.zeros(student_rows)
                for i in np.arange(student_rows):
                    row_i = cluster_r_data.iloc[i]

                    # euclidean distance:
                    diff_row = (row_i.subtract(centroid_r))
                    diff_row = np.square(diff_row)

                    summed_pair = diff_row.sum()

                    D_r_array[i] = summed_pair

                # *Calculate contribution to WSS and weighted WSS
                # note: D_r should strictly be this sum * 2n_r, but WSS then divides by 2 n_r - save operation by omitting the sum here
                D_r = sum(D_r_array)

                # sqrt if we want non-squared euclidean distance
                # D_r = np.sqrt(D_r)

                cluster_r_contribution = D_r

                # note: the weighted gap version would be (2*n_r * (n_r - 1)). Here, the 2 n_r already accounted for;
                weighed_cluster_r_contribution = D_r/(student_rows - 1)

            if distance_measure == 'mahalanobis':
                # calculate covariance of the entire cluster
                V_matcov = np.cov(cluster_r_data, rowvar=False)


                # calculate covariance of the entire dataset - prevents singular matrices
                V_matcov = np.cov(data, rowvar=False)

                # invert
                inv_V_matcov = scipy.linalg.inv(V_matcov)

                D_r_array = np.zeros(student_rows)
                for i in np.arange(student_rows):
                    # print(i)
                    row_i = cluster_r_data.iloc[i]

                    # invert
                    inv_V_matcov = scipy.linalg.inv(V_matcov)



                    # calcilate mahalanobis distance between this row, the centroid, using the inverse covariance matrix
                    mahal_dist = scipy.spatial.distance.mahalanobis(row_i, centroid_r, inv_V_matcov)

                    D_r_array[i] = mahal_dist


                # * Calculate contribution to WSS and weighted WSS

                # note: D_r should strictly be this sum * 2n_r, but WSS then divides by 2 n_r - save operation by omitting the sum here
                D_r = sum(D_r_array)

                # sqrt if we want non-squared euclidean distance
                # D_r = np.sqrt(D_r)

                cluster_r_contribution = D_r

                # note: the weighted gap version would be (2*n_r * (n_r - 1)). Here, the 2 n_r already accounted for;
                # print(student_rows)
                weighed_cluster_r_contribution = D_r/(student_rows - 1)

        else: #calculate by comparing i/i' pairs within cluster
            D_r = 0 #sum for this cluster

            # generate all i/i' pairs
            student_rowcombs = itertools.combinations(np.arange(0,student_rows), 2)

            student_rowcombs_array = list(student_rowcombs)

            def _D_r_calculator(iiprime):
                row1 = iiprime[0]
                row2 = iiprime[1]

                # extract the row i and row i'
                row1_data = cluster_r_data.iloc[row1]
                row2_data = cluster_r_data.iloc[row2]

                # calculate distance between them i.e. essentially a simple dissimilarity comparison here if binary
                diff_row = (row1_data.subtract(row2_data))
                diff_row = np.square(diff_row) #square according to Euclidean distance

                summed_pair = diff_row.sum()
                return summed_pair

            # generating this array is the bottleneck here; if the student row_combs_array is large
            D_r_array = map(_D_r_calculator, student_rowcombs_array)
            D_r_array = list(D_r_array)
            D_r = sum(D_r_array)

            # add this contribution from this cluster to WSS, scaled appropriately for number of students in the cluster
            cluster_r_contribution = D_r / (2*student_rows)


            #weighted gap
            weighed_cluster_r_contribution = D_r / (2*student_rows*(student_rows - 1))

        W_nclust += cluster_r_contribution
        weighted_W_nclust += weighed_cluster_r_contribution
        # print("Contribution %r" %cluster_r_contribution)

    if verbosity:
        print("Done calculating WSS!")
        # exit()

    return W_nclust, weighted_W_nclust


def clusterer(data, clust_method, nclust, verbosity=True):
    '''Clusters data according to the chosen method.
    Currently implemented to use with:
        - kmodes
        - kmeans
        - dbscan

    Args:
        data (pandas DataFrame): question only data of students (rows) and binary question performance (columns).
        clust_method (string): calls a particular clustering method
        nclust  (int): number of partitions to split dataset into)
        verbosity (bool, optional): _description_. Defaults to True.

    Returns:
        clusters, centroids: an array of which index in data belongs to which cluster, and the centroids for each cluster.
    '''

    # check that the specified clustering method is one that we expect
    valid_clust_methods = {'kmodes', 'kmeans', 'dbscan'}
    if clust_method not in valid_clust_methods:
        raise ValueError("results: status must be one of %r." % valid_clust_methods)

    if verbosity:
        print("Clustering with method: %r" % clust_method)

    # perform clustering
    if clust_method == 'kmodes':
        #* define particular args for the KModes function
        # init_method = "Huang"
        init_method = "Cao"
        n_init = 10
        verbosity=0

        # run kmodes
        km = KModes(n_clusters=nclust, init=init_method, n_init=n_init, verbose=verbosity)

        # fit the model -
        clusters = km.fit_predict(data)

        centroids = km.cluster_centroids_

    if clust_method == 'kmeans':
        # create Kmeans clustering instance
        km = sklearn.cluster.KMeans(n_clusters = nclust, n_init = 100, init='k-means++', tol = 1e-6, random_state=0, algorithm="full")
        # apply on the dataset
        km.fit(data)
        # get clusters, centroids and inertia
        clusters = km.predict(data)

        centroids = km.cluster_centers_

        inertia = km.inertia_

    if clust_method == 'dbscan':
        # create instance
        model = sklearn.cluster.DBSCAN(eps=1.8, min_samples=20)

        clusters = model.fit_predict(data)

        # TODO: calculate the centroids from this; some average of each.

    return clusters, centroids



def tsne_visualizer(data, nclust, clust_method='kmeans', viz_dimensions = 2, image_dir="~", plot_clusters=False, interactive_3d = True):
    '''Helper function to visualizes the student data in higher dimensions using t-SNE.

    Args:
        data (pd df or np array): dataset to be visualized
        nclust (int): how many clusters we partition this dataset into
        clust_method (str): which clustering method we use to perform partitioning
        viz_dimensions (int, optional): How many dims we want to see (2 or 3D visualization). Defaults to 2.
        image_dir (str, optional): Location to save figures to. Defaults to "~".
        plot_clusters (bool, optional): True if we want to additionally plot the dataset coloured according to cluster membership. Defaults to False.
        interactive_3d (bool, optional): True if we want to use plt.show() to play with the visualization e.g. rotation. Defaults to True.
    '''

    t = time.localtime()
    loctime = time.strftime("%H:%M:%S", t)
    print("Visualization with t-SNE at {0}".format(loctime))
    perp = 40

    # for perp in np.arange(0, 105, 5):
    for perp in [perp]:
        tsne_model = sklearn.manifold.TSNE(n_components=viz_dimensions, learning_rate='auto', init='random', perplexity=perp)

        embedded_data = tsne_model.fit_transform(data)

        # 2d plot
        if viz_dimensions == 2:
            x = embedded_data[:, 0]
            y = embedded_data[:, 1]

            fig, ax = plt.subplots()

            # standard
            ax.plot(x,y, linestyle="", marker=".", markersize=2)

            # density based-colourized
            from scipy.stats import gaussian_kde
            xy = np.vstack([x,y])
            z = gaussian_kde(xy)(xy)
            idx = z.argsort()
            x, y, z = x[idx], y[idx], z[idx]

            ax.set_title("t-SNE - perplexity {0}".format(perp))
            ax.set_xlabel("Comp. 1")
            ax.set_ylabel("Comp. 2")

            fig.savefig(image_dir + "/tsne_viz.pdf", bbox_inches='tight')

            # seaborn density plot
            sns.kdeplot(x=x,y=y, cmap="Reds", shade=True, bw_adjust=.5)

        if viz_dimensions == 3:

            x = embedded_data[:, 0]
            y = embedded_data[:, 1]
            z = embedded_data[:, 2]

            plt.close()
            fig = plt.figure(figsize=(10, 6))
            ax = plt.axes(projection='3d')

            ax.scatter(x, y, z)

            ax.set_xlabel("Comp. 1")
            ax.set_ylabel("Comp. 2")
            ax.set_zlabel("Comp. 3")

        plt.savefig(image_dir + "/sns_tsne_viz.pdf", bbox_inches='tight')

        # cluster and plot the results - excellent for visualization which cluster is which
        if plot_clusters:
            if viz_dimensions == 2:
                fig2, ax2 = plt.subplots()
                ax2.set_title("t-SNE - Clusters")
                ax2.set_xlabel("Comp. 1")
                ax2.set_ylabel("Comp. 2")

                # cluster students into 'nclust' distinct clusters

                clusters, centroids = clusterer(data, clust_method, nclust, verbosity = False)



                tsne_model = sklearn.manifold.TSNE(n_components=viz_dimensions, learning_rate='auto', init='pca', perplexity=perp)

                tsne_results = tsne_model.fit_transform(data)

                x = tsne_results[:, 0]
                y = tsne_results[:, 1]


                cmap = plt.cm.get_cmap('Set3')
                # cmap = plt.cm.get_cmap('Set1')
                scatterpoint_size = 6
                ax2.scatter(x,y, c=clusters, s=scatterpoint_size, cmap=cmap)

                sns.kdeplot(x=x,y=y, cmap="Reds", shade=True, bw_adjust=.5, zorder=-1)

                txts = []
                import matplotlib.patheffects as PathEffects
                for i in range(nclust):

                    # Position of each label at median of data points.

                    xtext, ytext = np.median(tsne_results[clusters == i, :], axis=0)
                    txt = ax2.text(xtext, ytext, str(i), fontsize=10)
                    txt.set_path_effects([
                        PathEffects.Stroke(linewidth=2, foreground="w"),
                        PathEffects.Normal()])
                    txts.append(txt)

            if viz_dimensions == 3:

                clusters, centroids = clusterer(data, clust_method, nclust, verbosity = False)

                x = embedded_data[:, 0]
                y = embedded_data[:, 1]
                z = embedded_data[:, 2]

                plt.close()
                fig2 = plt.figure()
                ax2 = plt.axes(projection='3d')

                ax2.set_xlabel("Comp. 1")
                ax2.set_ylabel("Comp. 2")
                ax2.set_zlabel("Comp. 3")

                cmap = plt.cm.get_cmap('Set3')
                cmap = plt.cm.get_cmap('tab10')
                scatterpoint_size = 12
                # ax2.scatter(x, y, z, c=clusters, s=scatterpoint_size, cmap=cmap)
                ax2.scatter(x, y, z, c=clusters, s=scatterpoint_size)

                if interactive_3d:
                    plt.show()

            fig2.savefig(image_dir + "/tsne_clustered_viz_perp{0}.pdf".format(perp), bbox_inches='tight')

    return


# Additional functions
def pca_visualizer(data, nclust, clust_method='kmeans', viz_dimensions = 2, project_dir="~" ):
    '''Visualizes the student data in higher dimensions using PSE.'''

    fig, (ax) = plt.subplots()
    print("Visualization with PCA")

    pca_model = sklearn.decomposition.PCA(n_components = viz_dimensions )

    princ_comps = pca_model.fit_transform(data)

    x = princ_comps[:, 0]
    y = princ_comps[:, 1]

    ax.plot(x, y, linestyle="", marker=".", markersize=4)


    ax.set_title("PCA")
    ax.set_xlabel("Comp. 1")
    ax.set_ylabel("Comp. 2")

    fig.savefig(project_dir + "/images/toy_viz.pdf", bbox_inches='tight')

    # cluster and now plot the clustered version

    fig2, ax2 = plt.subplots()
    ax2.set_title("PCA - Clusters")
    ax2.set_xlabel("Comp. 1")
    ax2.set_ylabel("Comp. 2")

    # cluster students into 'nclust' distinct clusters

    clusters, centroids = clusterer(data, clust_method, nclust, verbosity = False)

    for i in np.arange(nclust):

        # extract the indices for students in cluster r
        cluster_r_indxs = [idx for idx in range(len(clusters)) if clusters[idx]==i]

        cluster_r_data = data.iloc[cluster_r_indxs]

        # print(cluster_r_data)

        # print()

        clustered_princ_comps = pca_model.transform(cluster_r_data)

        # print(cluster_r_data)

        x = clustered_princ_comps[:, 0]
        y = clustered_princ_comps[:, 1]

        ax2.plot(x, y, linestyle="", marker=".", markersize=4)


    fig2.savefig(project_dir + "/images/clustered_toy_viz.pdf", bbox_inches='tight')

    return princ_comps, pca_model


def xmeans_clustering(data):
    '''Additional function to perform xmeans clustering - do not need to provide any parameters and supposedly tunes the optimal value of k automatically. Has not been thoroughly tested yet.'''
    import pyclustering.cluster.xmeans as pyxmeans

    # have to turn of C/C++ imp since on different architecture
    model = pyxmeans.xmeans(data, ccore=False, repeat=1000)

    model.process()

    clusters = model.get_clusters()
    centers = model.get_centers()

    return

