import sys
import numpy as np
import pandas as pd
import altair as alt
import matplotlib.pyplot as plt
import shap
from sklearn.cluster import KMeans
from gap_statistic import OptimalK
import matplotlib.pyplot as plt
import seaborn as sns
#%matplotlib inline
file_name = sys.argv[1]
df = pd.read_csv(file_name, header=None)
columns = df.columns
df = df.fillna(0)
df.head()
n_clusters = int(sys.argv[2])
km = KMeans(n_clusters=n_clusters, random_state=2021).fit(df)
preds = km.predict(df)
groups = df.copy(deep=True)
groups['group'] = preds
groups = groups.groupby('group').mean()
index = pd.Series(groups.mean(axis=1).sort_values(ascending=False).index).to_dict()
index = {value:key for (key,value) in index.items()}
preds_ordered = pd.DataFrame(preds, columns=['group']).replace({"group": index})
group_statistics = df.copy(deep=True)
preds = preds_ordered['group'].values
group_statistics['Group'] = preds + 1
group_statistics = group_statistics.groupby('Group').mean()
sns.heatmap(group_statistics.T, cmap="RdYlGn")
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
X_train, X_test, y_train, y_test = train_test_split(df, preds, test_size=0.3)
clf=RandomForestClassifier(n_estimators=200)
clf.fit(X_train,y_train)
feature_imp = pd.Series(clf.feature_importances_,index=df.columns).sort_values(ascending=False)
print(feature_imp.to_string())