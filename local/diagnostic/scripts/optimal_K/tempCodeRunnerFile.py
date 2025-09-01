# extract all optimalK information to output 
        # '''1) Vanilla gap'''
        # # unpack values of interest
        # gap_arr = self.gap_res.gap_k_array
        # gap_optK_index = self.gap_res.optK_pred_indx
        
        # gap_ax.plot(nclust_array, gap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col )
        # # plot the max
        # gap_ax.plot(nclust_array[gap_optK_index], gap_arr[gap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')
        
        
        # '''2) Weighted gap'''
        
        # wgap_arr = self.wgap_res.weighted_gap_k_array
        # wgap_optK_index = self.wgap_res.optK_pred_indx
        
        # wgap_ax.plot(nclust_array, wgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # wgap_ax.plot(nclust_array[wgap_optK_index], wgap_arr[wgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')
        
        
        # '''3) DD-Vanilla gap'''
        # ddgap_arr = self.dd_gap_res.dd_gap_k_array
        # ddgap_optK_index = self.dd_gap_res.optK_pred_indx
        
        # ddgap_ax.plot(nclust_array, ddgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # ddgap_ax.plot(nclust_array[ddgap_optK_index], ddgap_arr[ddgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')
        
        
        # '''4) DD-Weighted gap'''
        # self.dd_wgap_res
        # ddwgap_arr = self.dd_wgap_res.dd_weighted_gap_k_array
        # ddwgap_optK_index = self.dd_wgap_res.optK_pred_indx
        
        # ddwgap_ax.plot(nclust_array, ddwgap_arr, marker=marker, markersize=markersize, linestyle=linestyle, color = col)
        # ddwgap_ax.plot(nclust_array[ddwgap_optK_index], ddwgap_arr[ddwgap_optK_index], marker=marker, markersize=markersize, linestyle=linestyle, color='red')