library(magrittr)
library(dplyr, warn.conflicts = FALSE)
library(tibble)
args <- commandArgs(TRUE)
filename <- args[1]
outputfilename <- args[2]
wd <- args[3]
clusters <- args[4]
nstart=50
setwd(wd)
petel.grades = read.csv(filename, check.names= T, header = F)
colnames(petel.grades)
petel.df = petel.grades
petel.df[petel.df=="NaN"] = "0"
petel.df = as.matrix(petel.df, nrow = dim(petel.df)[1], ncol = dim(petel.df)[2])
class(petel.df) = "numeric"
set.seed(2020)

############# BEGIN NEW CODE ############################
library(cluster)
clusters = with(clusGap(petel.df, kmeans, 10, nstart=nstart), maxSE(Tab[,"gap"],Tab[,"SE.sim"], method = "firstSEmax", SE.factor = 2))
############# END NEW CODE ############################

petel.km.res = petel.df%>% dist(method = "euclidean") %>% kmeans(clusters, nstart=nstart)
petel.df = round(t(t(petel.df)/apply(petel.df,2,max)), 2)
student.groups = petel.df%>% as.data.frame() %>%mutate(group = petel.km.res$cluster)
student.groups$id = petel.grades$UserID
student.groups$grade = petel.grades$CourseIDs
student.groups$class = petel.grades$ActivityIDs
group.df = student.groups%>%
  group_by(group) %>%
  summarise_all(mean)%>%
  column_to_rownames("group")%>%
  mutate_all(round,2) %>% t()
groups_order = order(colSums(-group.df , na.rm=T))
group.df <-group.df [, order(colSums(-group.df , na.rm=T))]
apply_new_order = match(1:clusters,groups_order)
student.groups$group = apply_new_order[student.groups$group]
petel.km.res$cluster = apply_new_order[petel.km.res$cluster]
write.csv(student.groups, outputfilename )